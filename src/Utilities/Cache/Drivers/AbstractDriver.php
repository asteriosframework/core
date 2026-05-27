<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Drivers;

use Asterios\Core\Contracts\Utilities\Cache\CacheDriverInterface;
use Asterios\Core\Contracts\Utilities\Cache\LockInterface;
use Asterios\Core\Contracts\Utilities\Cache\SerializerInterface;

abstract class AbstractDriver implements CacheDriverInterface
{
    public function __construct(
        protected readonly SerializerInterface $serializer,
        protected readonly string $prefix = 'asterios:',
    ) {
    }

    abstract public function get(string $key): mixed;

    abstract public function set(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool;

    abstract public function delete(string $key): bool;

    abstract public function clear(): bool;

    abstract public function increment(
        string $key,
        int $step = 1
    ): int|false;

    abstract public function decrement(
        string $key,
        int $step = 1
    ): int|false;

    public function add(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        $lock = $this->lock('add:' . $key, 5);

        if (!$lock->isAcquired())
        {
            return false;
        }

        try
        {
            if ($this->has($key))
            {
                return false;
            }

            return $this->set($key, $value, $ttl);
        }
        finally
        {
            $lock->release();
        }
    }

    abstract public function lock(
        string $name,
        int $ttl = 10
    ): LockInterface;

    abstract public function isAvailable(): bool;

    protected function namespacedKey(string $key): string
    {
        return $this->prefix . $key;
    }

    protected function tagKey(string $tag): string
    {
        return $this->prefix . 'tag:' . $tag;
    }

    protected function lockKey(string $name): string
    {
        return $this->prefix . 'lock:' . $name;
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

    public function setMultiple(iterable $values, ?int $ttl = null): bool
    {
        $ok = true;

        foreach ($values as $key => $value)
        {
            $ok = $ok && $this->set((string)$key, $value, $ttl);
        }

        return $ok;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $ok = true;

        foreach ($keys as $key)
        {
            $ok = $ok && $this->delete((string)$key);
        }

        return $ok;
    }

    public function flushTag(string $tag): bool
    {
        return $this->incrementTagVersion($tag) > 0;
    }

    public function supportsTags(): bool
    {
        return true;
    }

    public function getTagVersion(string $tag): int
    {
        $version = $this->get($this->tagKey($tag));

        if (!is_int($version))
        {
            $this->set($this->tagKey($tag), 1);
            return 1;
        }

        return $version;
    }

    public function incrementTagVersion(string $tag): int
    {
        $result = $this->increment($this->tagKey($tag));

        if ($result === false)
        {
            $this->set($this->tagKey($tag), 1);
            return 1;
        }

        return $result;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }
}