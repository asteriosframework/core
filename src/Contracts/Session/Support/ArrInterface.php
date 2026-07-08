<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Session\Support;

interface ArrInterface
{
    /**
     * @param array $source
     * @param string|array|null $key
     * @param array|string|int|float|bool|null $default
     * @return array|string|int|float|bool|null
     */
    public static function get(array $source, string|array|null $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null;

    /**
     * @param array $source
     * @param string|array|null $key
     * @param array|string|int|float|bool|null $value
     * @return void
     */
    public static function set(array &$source, string|array|null $key, array|string|int|float|bool|null $value = null): void;

    /**
     * @param array<string, mixed> $source
     * @param string $key
     * @return bool
     */
    public static function has(array $source, string $key): bool;

    /**
     * @param array<string, mixed> $source
     * @param string $key
     * @return void
     */
    public static function forget(array &$source, string $key): void;
}
