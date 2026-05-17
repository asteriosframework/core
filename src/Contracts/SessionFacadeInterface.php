<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

use Asterios\Core\Contracts\Session\SessionInterface;

interface SessionFacadeInterface
{
    /**
     * @return self
     */
    public static function forge(): self;

    /**
     * @param SessionInterface $session
     * @return void
     */
    public static function setInstance(SessionInterface $session): void;

    /**
     * @return SessionInterface
     */
    public static function instance(): SessionInterface;

    /**
     * @return bool
     */
    public static function exists(): bool;

    /**
     * @return bool
     */
    public static function isStarted(): bool;

    /**
     * @param string|array|null $key
     * @param array|string|int|float|bool|null $default
     * @return array|string|int|float|bool|null
     */
    public static function get(string|array|null $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null;

    /**
     * @param string|array|null $key
     * @param array|string|int|float|bool|null $value
     * @return void
     */
    public static function set(string|array|null $key, array|string|int|float|bool|null $value = null): void;

    /**
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool;

    /**
     * @param string $key
     * @return void
     */
    public static function remove(string $key): void;

    /**
     * @return void
     */
    public static function clear(): void;

    /**
     * @param string $key
     * @param array|string|int|float|bool|null $default
     * @return array|string|int|float|bool|null
     */
    public static function pull(string $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null;

    /**
     * @param bool $destroy
     * @return bool
     */
    public static function regenerate(bool $destroy = true): bool;

    /**
     * @return void
     */
    public static function destroy(): void;

    /**
     * @return void
     */
    public static function invalidate(): void;

    /**
     * @return void
     */
    public static function reset(): void;

    /**
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public static function getString(string $key, ?string $default = null): ?string;

    /**
     * @param string $key
     * @param int|null $default
     * @return int|null
     */
    public static function getInt(string $key, ?int $default = null): ?int;

    /**
     * @param string $key
     * @param float|null $default
     * @return float|null
     */
    public static function getFloat(string $key, ?float $default = null): ?float;

    /**
     * @param string $key
     * @param bool|null $default
     * @return bool|null
     */
    public static function getBool(string $key, ?bool $default = null): ?bool;

    /**
     * @param string $key
     * @param array<array-key, mixed> $default
     * @return array<array-key, mixed>
     */
    public static function getArray(string $key, array $default = []): array;

    /**
     * @param string $key
     * @param array|string|int|float|bool|null $value
     * @return void
     */
    public static function flash(string $key, array|string|int|float|bool|null $value): void;

    /**
     * @param string $key
     * @param array|string|int|float|bool|null $default
     * @return array|string|int|float|bool|null
     */
    public static function getFlash(string $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null;

    /**
     * @param string $key
     * @return bool
     */
    public static function hasFlash(string $key): bool;

    /**
     * @param string|array $keys
     * @return void
     */
    public static function keepFlash(string|array $keys): void;

    /**
     * @return void
     */
    public static function reflash(): void;

    /**
     * @return void
     */
    public static function clearFlash(): void;

    /**
     * @param string $key
     * @param array|string|int|float|bool|null $value
     * @param int $ttlSeconds
     * @return void
     */
    public static function putWithTtl(string $key, array|string|int|float|bool|null $value, int $ttlSeconds): void;

    /**
     * @param string $key
     * @return bool
     */
    public static function hasExpired(string $key): bool;

    /**
     * @return void
     */
    public static function purgeExpired(): void;
}