<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

use Asterios\Core\Exception\CollectionException;
use Closure;

interface CollectionInterface
{
    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param mixed $item
     * @return self
     */
    public function add(mixed $item): self;

    /**
     * @param Closure $callback
     * @return self
     */
    public function map(Closure $callback): self;

    /**
     * @param Closure $callback
     * @return self
     */
    public function filter(Closure $callback): self;

    /**
     * @param Closure $callback
     * @param $initial
     * @return mixed
     */
    public function reduce(Closure $callback, $initial = null): mixed;

    /**
     * @param Closure|null $callback
     * @param $default
     * @return mixed
     */
    public function first(Closure $callback = null, $default = null): mixed;

    /**
     * @return string
     * @throws CollectionException
     */
    public function toJson(): string;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return object
     */
    public function toObject(): object;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return array
     */
    public function flip(): array;

    /**
     * @return int
     */
    public function sum(): int;

    /**
     * @param bool $preserveKeys
     * @return array
     */
    public function reverse(bool $preserveKeys = false): array;

    /**
     * @param bool $withoutDecimal
     * @return float|int
     */
    public function avg(bool $withoutDecimal = false): float|int;

    /**
     * @return bool
     */
    public function hasItems(): bool;

    /**
     * @param string|array $items
     * @return bool
     */
    public function has(string|array $items): bool;

    /**
     * @param mixed $item
     * @return mixed
     */
    public function getItem(mixed $item): mixed;

    /**
     * @param mixed $item
     * @param mixed $value
     * @return void
     */
    public function setItem(mixed $item, mixed $value): void;

    /**
     * @param mixed $item
     * @return void
     */
    public function unsetItem(mixed $item): void;

    /**
     * @param string $item
     * @param Closure $callback
     * @return mixed
     */
    public function findIn(string $item, Closure $callback): mixed;
}