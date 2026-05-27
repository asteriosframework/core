<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Drivers;

use Asterios\Core\Contracts\Utilities\Cache\LockInterface;
use Asterios\Core\Contracts\Utilities\Cache\SerializerInterface;
use Asterios\Core\Utilities\Cache\Support\ApcuLock;

final class ApcuDriver extends AbstractDriver
{
    public function __construct(
        SerializerInterface $serializer,
        string $prefix = 'asterios:',
    ) {
        parent::__construct($serializer, $prefix);
    }

    public function get(string $key): mixed
    {
        $success = false;

        $value = apcu_fetch(
            $this->namespacedKey($key),
            $success
        );

        if (!$success)
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
        return apcu_store(
            $this->namespacedKey($key),
            $this->serializer->serialize($value),
            $ttl ?? 0
        );
    }

    public function delete(string $key): bool
    {
        return apcu_delete(
            $this->namespacedKey($key)
        );
    }

    public function clear(): bool
    {
        return apcu_clear_cache();
    }

    public function increment(
        string $key,
        int $step = 1
    ): int|false {
        $cacheKey = $this->namespacedKey($key);

        if (!apcu_exists($cacheKey))
        {
            apcu_add($cacheKey, $this->serializer->serialize(0));
        }

        $success = false;

        $value = apcu_fetch($cacheKey, $success);

        if (!$success)
        {
            return false;
        }

        $current = (int) $this->serializer->unserialize($value);
        $current += $step;

        apcu_store(
            $cacheKey,
            $this->serializer->serialize($current)
        );

        return $current;
    }

    public function decrement(
        string $key,
        int $step = 1
    ): int|false {
        return $this->increment($key, -$step);
    }

    public function add(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        return apcu_add(
            $this->namespacedKey($key),
            $this->serializer->serialize($value),
            $ttl ?? 0
        );
    }

    public function lock(
        string $name,
        int $ttl = 10
    ): LockInterface {
        $lock = new ApcuLock(
            $this->lockKey($name)
        );

        $lock->acquire($ttl);

        return $lock;
    }

    public function isAvailable(): bool
    {
        if (!extension_loaded('apcu'))
        {
            return false;
        }

        if (function_exists('apcu_enabled'))
        {
            return apcu_enabled();
        }

        return filter_var(
            ini_get('apc.enabled'),
            FILTER_VALIDATE_BOOL
        );
    }

    public function getMultiple(iterable $keys): array
    {
        $mapping = [];

        foreach ($keys as $key)
        {
            $mapping[(string)$key] = $this->namespacedKey((string)$key);
        }

        $fetched = apcu_fetch(array_values($mapping));
        $result = [];

        foreach ($mapping as $original => $cacheKey)
        {
            if (!array_key_exists($cacheKey, $fetched))
            {
                $result[$original] = null;
                continue;
            }

            $result[$original] = $this->serializer->unserialize(
                $fetched[$cacheKey]
            );
        }

        return $result;
    }

    public function setMultiple(
        iterable $values,
        ?int $ttl = null
    ): bool {
        $payload = [];

        foreach ($values as $key => $value)
        {
            $payload[$this->namespacedKey((string)$key)] =
                $this->serializer->serialize($value);
        }

        $failed = apcu_store(
            $payload,
            null,
            $ttl ?? 0
        );

        return $failed === [];
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $cacheKeys = [];

        foreach ($keys as $key)
        {
            $cacheKeys[] = $this->namespacedKey((string)$key);
        }

        $failed = apcu_delete($cacheKeys);

        return $failed === [];
    }
}