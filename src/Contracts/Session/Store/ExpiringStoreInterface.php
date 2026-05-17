<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Session\Store;

interface ExpiringStoreInterface
{
    /**
     * @param array<string, mixed> $session
     * @return void
     */
    public function initialize(array &$session): void;

    /**
     * @param array<string, mixed> $session
     * @param string $key
     * @param array|string|int|float|bool|null $value
     * @param int $ttlSeconds
     */
    public function put(array &$session, string $key, array|string|int|float|bool|null $value, int $ttlSeconds): void;

    /**
     * @param array<string, mixed> $session
     * @param string $key
     * @return bool
     */
    public function hasExpired(array &$session, string $key): bool;

    /**
     * @param array<string, mixed> $session
     * @return void
     */
    public function purge(array &$session): void;

    /**
     * @param array<string, mixed> $session
     * @return void
     */
    public function clear(array &$session): void;

}
