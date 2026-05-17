<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Session;

interface FlashBagInterface
{
    /**
     * @param array<string, mixed> $session
     * @param string $key
     * @param array|string|int|float|bool|null $value
     */
    public function flash(array &$session, string $key, array|string|int|float|bool|null $value): void;

    /**
     * @param array<string, mixed> $session
     * @param string $key
     * @param array|string|int|float|bool|null $default
     * @return array|string|int|float|bool|null
     */
    public function get(array &$session, string $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null;

    /**
     * @param array<string, mixed> $session
     * @param string $key
     * @return bool
     */
    public function has(array &$session, string $key): bool;

    /**
     * @param array<string, mixed> $session
     * @param string|list<string> $keys
     * @return void
     */
    public function keep(array &$session, string|array $keys): void;

    /**
     * @param array<string, mixed> $session
     * @return void
     */
    public function reflash(array &$session): void;

    /**
     * @param array<string, mixed> $session
     * @return void
     */
    public function clear(array &$session): void;

    /**
     * @param array<string, mixed> $session
     * @return void
     */
    public function age(array &$session): void;
}
