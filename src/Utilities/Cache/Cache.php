<?php

declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache;

use Asterios\Core\Contracts\Utilities\Cache\CacheDriverInterface;
use Closure;

final class Cache
{
    private static ?self $instance = null;

    private array $tags = [];

    public function __construct(
        private readonly CacheDriverInterface $driver,
        private readonly int $defaultTtl = 3600,
    ) {
    }

    public static function forge(): self
    {
        if (self::$instance !== null)
        {
            return self::$instance;
        }

        self::$instance = CacheFactory::fromConfig();

        return self::$instance;
    }

    public function get(
        string $key,
        mixed $default = null
    ): mixed {
        $value = $this->driver->get(
            $this->taggedKey($key)
        );

        return $value ?? $default;
    }

    public function set(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        return $this->driver->set(
            $this->taggedKey($key),
            $value,
            $ttl ?? $this->defaultTtl
        );
    }

    public function delete(string $key): bool
    {
        return $this->driver->delete(
            $this->taggedKey($key)
        );
    }

    public function clear(): bool
    {
        return $this->driver->clear();
    }

    public function has(string $key): bool
    {
        return $this->driver->has(
            $this->taggedKey($key)
        );
    }

    public function missing(string $key): bool
    {
        return !$this->has($key);
    }

    public function pull(
        string $key,
        mixed $default = null
    ): mixed {
        $value = $this->get($key, $default);

        $this->delete($key);

        return $value;
    }

    public function add(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        return $this->driver->add(
            $this->taggedKey($key),
            $value,
            $ttl ?? $this->defaultTtl
        );
    }

    public function increment(
        string $key,
        int $step = 1
    ): int|false {
        return $this->driver->increment(
            $this->taggedKey($key),
            $step
        );
    }

    public function decrement(
        string $key,
        int $step = 1
    ): int|false {
        return $this->driver->decrement(
            $this->taggedKey($key),
            $step
        );
    }

    public function remember(
        string $key,
        Closure $callback,
        ?int $ttl = null,
        bool $lock = false
    ): mixed {
        $existing = $this->get($key);

        if ($existing !== null)
        {
            return $existing;
        }

        if (!$lock)
        {
            $value = $callback();
            $this->set($key, $value, $ttl);

            return $value;
        }

        $cacheLock = $this->driver->lock(
            'remember:' . $key,
            10
        );

        if (!$cacheLock->isAcquired())
        {
            $attempts = 20;

            while ($attempts-- > 0)
            {
                usleep(100000);

                $existing = $this->get($key);

                if ($existing !== null)
                {
                    return $existing;
                }
            }

            $cacheLock = $this->driver->lock(
                'remember:' . $key,
                10
            );

            if (!$cacheLock->isAcquired())
            {
                return $callback();
            }
        }

        try
        {
            $existing = $this->get($key);

            if ($existing !== null)
            {
                return $existing;
            }

            $value = $callback();

            $this->set($key, $value, $ttl);

            return $value;
        }
        finally
        {
            $cacheLock->release();
        }
    }

    public function rememberForever(
        string $key,
        Closure $callback,
        bool $lock = false
    ): mixed {
        $existing = $this->get($key);

        if ($existing !== null)
        {
            return $existing;
        }

        if (!$lock)
        {
            $value = $callback();

            $this->driver->set(
                $this->taggedKey($key),
                $value,
                null
            );

            return $value;
        }

        $cacheLock = $this->driver->lock(
            'remember_forever:' . $key,
            10
        );

        if (!$cacheLock->isAcquired())
        {
            return $this->get($key);
        }

        try
        {
            $existing = $this->get($key);

            if ($existing !== null)
            {
                return $existing;
            }

            $value = $callback();

            $this->driver->set(
                $this->taggedKey($key),
                $value,
                null
            );

            return $value;
        }
        finally
        {
            $cacheLock->release();
        }
    }

    public function getOrSet(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): mixed {
        $existing = $this->get($key);

        if ($existing !== null)
        {
            return $existing;
        }

        if ($this->add($key, $value, $ttl))
        {
            return $value;
        }

        return $this->get($key);
    }

    public function getMultiple(iterable $keys): array
    {
        $result = [];

        foreach ($keys as $key)
        {
            $result[(string)$key] = $this->get((string)$key);
        }

        return $result;
    }

    public function setMultiple(
        iterable $values,
        ?int $ttl = null
    ): bool {
        $tagged = [];

        foreach ($values as $key => $value)
        {
            $tagged[$this->taggedKey((string)$key)] = $value;
        }

        return $this->driver->setMultiple(
            $tagged,
            $ttl ?? $this->defaultTtl
        );
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $tagged = [];

        foreach ($keys as $key)
        {
            $tagged[] = $this->taggedKey((string)$key);
        }

        return $this->driver->deleteMultiple($tagged);
    }

    public function tags(array $tags): self
    {
        $clone = clone $this;

        $clone->tags = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn ($tag) => trim((string)$tag),
                        $tags
                    )
                )
            )
        );

        return $clone;
    }

    public function flushTags(): bool
    {
        $ok = true;

        foreach ($this->tags as $tag)
        {
            $ok = $this->driver->flushTag($tag) && $ok;
        }

        return $ok;
    }

    private function taggedKey(string $key): string
    {
        if ($this->tags === [])
        {
            return $key;
        }

        $versions = [];

        foreach ($this->tags as $tag)
        {
            $versions[] = $tag . ':' . $this->driver->getTagVersion($tag);
        }

        return implode('|', $versions) . '|' . $key;
    }
}
