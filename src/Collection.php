<?php declare(strict_types=1);

namespace Asterios\Core;

use ArrayAccess;
use ArrayIterator;
use Asterios\Core\Exception\CollectionException;
use Closure;
use Countable;
use IteratorAggregate;

class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public static function forge(array $items = []): self
    {
        return new self($items);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function add($item): self
    {
        $this->items[] = $item;

        return $this;
    }

    public function map(Closure $callback): self
    {
        $this->items = array_map($callback, $this->items);

        return $this;
    }

    public function filter(Closure $callback): self
    {
        $this->items = array_filter($this->items, $callback);

        return $this;
    }

    public function reduce(Closure $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    public function first(Closure $callback = null, $default = null)
    {
        foreach ($this->items as $item)
        {
            if ($callback === null || $callback($item))
            {
                return $item;
            }
        }

        return $default;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === null)
        {
            $this->items[] = $value;
        }
        else
        {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return string
     * @throws CollectionException
     */
    public function toJson(): string
    {
        try
        {
            return json_encode($this->items, JSON_THROW_ON_ERROR);
        }
        catch (\JsonException $e)
        {
            throw new CollectionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function flip(): array
    {
        return array_flip($this->items);
    }

    public function sum(): int
    {
        return array_sum($this->items);
    }

    public function reverse(bool $preserveKeys = false): array
    {
        return array_reverse($this->items, $preserveKeys);
    }

    public function avg(bool $withoutDecimal = false): float|int
    {
        $average = 0;

        $totalEntries = $this->count();

        if ($this->count() > 0)
        {
            $average = $this->sum() / $totalEntries;
        }

        return ($withoutDecimal) ? (int)$average : $average;
    }
}