<?php

declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Drivers;

use Asterios\Core\Contracts\Utilities\Cache\LockInterface;
use Asterios\Core\Contracts\Utilities\Cache\SerializerInterface;
use Asterios\Core\Utilities\Cache\Support\RedisLock;
use Redis;
use RedisException;

final class RedisDriver extends AbstractDriver
{
    public function __construct(
        private readonly Redis $redis,
        SerializerInterface $serializer,
        string $prefix = 'asterios:',
    ) {
        parent::__construct($serializer, $prefix);
    }

    public function get(string $key): mixed
    {
        $value = $this->redis->get($this->namespacedKey($key));

        if ($value === false)
        {
            return null;
        }

        return $this->serializer->unserialize($value);
    }

    public function set(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        $payload = $this->serializer->serialize($value);
        $cacheKey = $this->namespacedKey($key);

        if ($ttl === null)
        {
            return $this->redis->set($cacheKey, $payload);
        }

        return $this->redis->setex($cacheKey, $ttl, $payload);
    }

    public function delete(string $key): bool
    {
        return $this->redis->del(
            $this->namespacedKey($key)
        ) > 0;
    }

    public function clear(): bool
    {
        $pattern = $this->prefix . '*';
        $iterator = null;

        try
        {
            while (($keys = $this->redis->scan($iterator, $pattern)) !== false)
            {
                if ($keys !== [])
                {
                    $this->redis->del($keys);
                }
            }

            return true;
        }
        catch (RedisException)
        {
            return false;
        }
    }

    public function increment(
        string $key,
        int $step = 1
    ): int|false {
        return $this->redis->incrBy(
            $this->namespacedKey($key),
            $step
        );
    }

    public function decrement(
        string $key,
        int $step = 1
    ): int|false {
        return $this->redis->decrBy(
            $this->namespacedKey($key),
            $step
        );
    }

    public function add(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        $payload = $this->serializer->serialize($value);
        $cacheKey = $this->namespacedKey($key);

        if ($ttl === null)
        {
            return (bool) $this->redis->set(
                $cacheKey,
                $payload,
                ['nx']
            );
        }

        return (bool) $this->redis->set(
            $cacheKey,
            $payload,
            [
                'nx',
                'ex' => $ttl,
            ]
        );
    }

    public function lock(string $name, int $ttl = 10): LockInterface
    {
        $lock = new RedisLock(
            $this->redis,
            $this->lockKey($name)
        );

        $lock->acquire($ttl);

        return $lock;
    }

    public function isAvailable(): bool
    {
        if (!extension_loaded('redis'))
        {
            return false;
        }

        try
        {
            return $this->redis->ping() !== false;
        }
        catch (RedisException)
        {
            return false;
        }
    }

    public function getMultiple(iterable $keys): array
    {
        $originalKeys = [];
        $redisKeys = [];

        foreach ($keys as $key)
        {
            $originalKeys[] = (string) $key;
            $redisKeys[] = $this->namespacedKey((string) $key);
        }

        $values = $this->redis->mget($redisKeys);
        $result = [];

        foreach ($originalKeys as $index => $key)
        {
            $value = $values[$index] ?? false;

            $result[$key] = $value === false
                ? null
                : $this->serializer->unserialize($value);
        }

        return $result;
    }

    public function setMultiple(
        iterable $values,
        ?int $ttl = null
    ): bool {
        if ($ttl === null)
        {
            $payload = [];

            foreach ($values as $key => $value)
            {
                $payload[$this->namespacedKey((string)$key)] =
                    $this->serializer->serialize($value);
            }

            return $this->redis->mset($payload);
        }

        $ok = true;

        foreach ($values as $key => $value)
        {
            $ok = $ok && $this->set(
                (string)$key,
                $value,
                $ttl
            );
        }

        return $ok;
    }
}