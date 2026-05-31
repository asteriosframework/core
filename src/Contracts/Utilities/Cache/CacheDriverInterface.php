<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Utilities\Cache;

interface CacheDriverInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * @return bool
     */
    public function clear(): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param iterable $keys
     * @return array
     */
    public function getMultiple(iterable $keys): array;

    /**
     * @param iterable $values
     * @param int|null $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, ?int $ttl = null): bool;

    /**
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool;

    /**
     * @param string $key
     * @param int $step
     * @return int|false
     */
    public function increment(string $key, int $step = 1): int|false;

    /**
     * @param string $key
     * @param int $step
     * @return int|false
     */
    public function decrement(string $key, int $step = 1): int|false;

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function add(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * @param string $name
     * @param int $ttl
     * @return LockInterface
     */
    public function lock(string $name, int $ttl = 10): LockInterface;

    /**
     * @return bool
     */
    public function supportsTags(): bool;

    /**
     * @param string $tag
     * @return bool
     */
    public function flushTag(string $tag): bool;

    /**
     * @param string $tag
     * @return int
     */
    public function getTagVersion(string $tag): int;

    /**
     * @param string $tag
     * @return int
     */
    public function incrementTagVersion(string $tag): int;

    /**
     * @return bool
     */
    public function isAvailable(): bool;
}
