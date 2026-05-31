<?php

declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache;

use Asterios\Core\Contracts\Utilities\Cache\CacheDriverInterface;
use Asterios\Core\Contracts\Utilities\Cache\LockInterface;

final class DriverChain implements CacheDriverInterface
{
    /**
     * @var array<string, int|null>
     */
    private array $ttlMap = [];

    /**
     * @param CacheDriverInterface[] $drivers
     */
    public function __construct(
        private readonly array $drivers,
    ) {
    }

    public function get(string $key): mixed
    {
        foreach ($this->drivers as $index => $driver)
        {
            $value = $driver->get($key);

            if ($value === null)
            {
                continue;
            }

            if ($index > 0)
            {
                $ttl = $this->ttlMap[$key] ?? null;

                for ($i = 0; $i < $index; $i++)
                {
                    $this->drivers[$i]->set(
                        $key,
                        $value,
                        $ttl
                    );
                }
            }

            return $value;
        }

        return null;
    }

    public function set(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        $ok = true;

        $this->ttlMap[$key] = $ttl;

        foreach ($this->drivers as $driver)
        {
            $ok = $driver->set($key, $value, $ttl) && $ok;
        }

        return $ok;
    }

    public function delete(string $key): bool
    {
        $ok = true;

        foreach ($this->drivers as $driver)
        {
            $ok = $driver->delete($key) && $ok;
        }

        unset($this->ttlMap[$key]);

        return $ok;
    }

    public function clear(): bool
    {
        $this->ttlMap = [];
        $ok = true;

        foreach ($this->drivers as $driver)
        {
            $ok = $driver->clear() && $ok;
        }

        return $ok;
    }

    public function has(string $key): bool
    {
        foreach ($this->drivers as $driver)
        {
            if ($driver->has($key))
            {
                return true;
            }
        }

        return false;
    }

    public function getMultiple(iterable $keys): array
    {
        $result = [];

        foreach ($keys as $key)
        {
            $result[$key] = $this->get((string)$key);
        }

        return $result;
    }

    public function setMultiple(
        iterable $values,
        ?int $ttl = null
    ): bool {
        $ok = true;

        foreach ($this->drivers as $driver)
        {
            $ok = $driver->setMultiple($values, $ttl) && $ok;
        }

        return $ok;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $keyList = [];

        foreach ($keys as $key)
        {
            $keyList[] = (string)$key;
            unset($this->ttlMap[(string)$key]);
        }

        $ok = true;

        foreach ($this->drivers as $driver)
        {
            $ok = $driver->deleteMultiple($keyList) && $ok;
        }

        return $ok;
    }

    public function increment(
        string $key,
        int $step = 1
    ): int|false {
        return $this->drivers[0]->increment($key, $step);
    }

    public function decrement(
        string $key,
        int $step = 1
    ): int|false {
        return $this->drivers[0]->decrement($key, $step);
    }

    public function add(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        if ($this->has($key))
        {
            return false;
        }

        return $this->set($key, $value, $ttl);
    }

    public function lock(
        string $name,
        int $ttl = 10
    ): LockInterface {
        return $this->drivers[0]->lock($name, $ttl);
    }

    public function supportsTags(): bool
    {
        return $this->drivers[0]->supportsTags();
    }

    public function flushTag(string $tag): bool
    {
        $ok = true;

        foreach ($this->drivers as $driver)
        {
            if ($driver->supportsTags())
            {
                $ok = $driver->flushTag($tag) && $ok;
            }
        }

        return $ok;
    }

    public function getTagVersion(string $tag): int
    {
        return $this->drivers[0]->getTagVersion($tag);
    }

    public function incrementTagVersion(string $tag): int
    {
        return $this->drivers[0]->incrementTagVersion($tag);
    }

    public function isAvailable(): bool
    {
        return $this->drivers !== [];
    }
}
