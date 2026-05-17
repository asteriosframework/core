<?php

declare(strict_types=1);

namespace Asterios\Core\Contracts\Session;

/**
 * @phpstan-type SessionScalar string|int|float|bool|null
 * @phpstan-type SessionValue SessionScalar|array<array-key, SessionValue>
 */
interface SessionInterface
{
    /**
     * @return void
     */
    public function start(): void;

    /**
     * @return bool
     */
    public function isStarted(): bool;

    /**
     * @return bool
     */
    public function exists(): bool;

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array;

    /**
     * @param string|list<string>|null $key
     * @param array<array-key, mixed>|string|int|float|bool|null $default
     *
     * @return array<array-key, mixed>|string|int|float|bool|null
     */
    public function get(string|array|null $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null;

    /**
     * @param string|array<string, array|string|int|float|bool|null>|null $key
     * @param array|string|int|float|bool|null $value
     */
    public function set(string|array|null $key, array|string|int|float|bool|null $value = null): void;

    /**
     * /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * @return void
     */
    public function remove(string $key): void;

    /**
     * @return void
     */
    public function clear(): void;

    /**
     * @param string $key
     * @param array|string|int|float|bool|null $default
     * @return array|string|int|float|bool|null
     */
    public function pull(string $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null;

    /**
     * @param bool $destroy
     * @return bool
     */
    public function regenerate(bool $destroy = true): bool;

    /**
     * @return void
     */
    public function invalidate(): void;

    /**
     * @return void
     */
    public function destroy(): void;

    /**
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function getString(string $key, ?string $default = null): ?string;

    /**
     * @param string $key
     * @param int|null $default
     * @return int|null
     */
    public function getInt(string $key, ?int $default = null): ?int;

    /**
     * @param string $key
     * @param float|null $default
     * @return float|null
     */
    public function getFloat(string $key, ?float $default = null): ?float;

    /**
     * @param string $key
     * @param bool|null $default
     * @return bool|null
     */
    public function getBool(string $key, ?bool $default = null): ?bool;

    /**
     * @param string $key
     * @param array<array-key, mixed> $default
     * @return array<array-key, mixed>
     */
    public function getArray(string $key, array $default = []): array;

    /**
     * @param string $key
     * @param array|string|int|float|bool|null $value
     * @return void
     */
    public function flash(string $key, array|string|int|float|bool|null $value): void;

    /**
     * @param string $key
     * @param array|string|int|float|bool|null $default
     * @return array|string|int|float|bool|null
     */
    public function getFlash(string $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null;

    /**
     * @param string $key
     * @return bool
     */
    public function hasFlash(string $key): bool;

    /**
     * @param string|list<string> $keys
     */
    public function keepFlash(string|array $keys): void;

    /**
     * @return void
     */
    public function reflash(): void;

    /**
     * @return void
     */
    public function clearFlash(): void;

    /**
     * @param string $key
     * @param array|string|int|float|bool|null $value
     * @param int $ttlSeconds
     * @return void
     */
    public function putWithTtl(string $key, array|string|int|float|bool|null $value, int $ttlSeconds): void;

    /**
     * @param string $key
     * @return bool
     */
    public function hasExpired(string $key): bool;

    /**
     * @return void
     */
    public function purgeExpired(): void;
}
