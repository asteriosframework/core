<?php declare(strict_types=1);

namespace Asterios\Core;

use ArrayAccess;
use ArrayIterator;
use Asterios\Core\Exception\CollectionException;
use Asterios\Core\Interfaces\CollectionInterface;
use Closure;
use Countable;
use IteratorAggregate;

class Collection implements CollectionInterface, ArrayAccess, Countable, IteratorAggregate
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

    /**
     * @inheritdoc
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function add($item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function map(Closure $callback): self
    {
        $this->items = array_map($callback, $this->items);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function filter(Closure $callback): self
    {
        $this->items = array_filter($this->items, $callback);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function reduce(Closure $callback, $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * @inheritdoc
     */
    public function first(Closure $callback = null, $default = null): mixed
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

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @inheritdoc
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

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return $this->items;
    }

    public function toObject(): object
    {
        return (object)$this->items;
    }

    /**
     * @inheritdoc
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * @inheritdoc
     */
    public function flip(): array
    {
        return array_flip($this->items);
    }

    /**
     * @inheritdoc
     */
    public function sum(): int
    {
        return array_sum($this->items);
    }

    /**
     * @inheritdoc
     */
    public function reverse(bool $preserveKeys = false): array
    {
        return array_reverse($this->items, $preserveKeys);
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function hasItems(): bool
    {
        return !$this->isEmpty();
    }
}