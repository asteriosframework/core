<?php declare(strict_types=1);

namespace Asterios\Core;

use ArrayAccess;
use ArrayIterator;
use Asterios\Core\Contracts\CollectionInterface;
use Asterios\Core\Exception\CollectionException;
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
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @inheritDoc
     */
    public function add($item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function map(Closure $callback): self
    {
        $this->items = array_map($callback, $this->items);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function filter(Closure $callback): self
    {
        $this->items = array_filter($this->items, $callback);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function reduce(Closure $callback, $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @inheritDoc
     */
    public function toObject(): object
    {
        return (object)$this->items;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * @inheritDoc
     */
    public function flip(): array
    {
        return array_flip($this->items);
    }

    /**
     * @inheritDoc
     */
    public function sum(): int
    {
        return array_sum($this->items);
    }

    /**
     * @inheritDoc
     */
    public function reverse(bool $preserveKeys = false): array
    {
        return array_reverse($this->items, $preserveKeys);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function hasItems(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * @inheritDoc
     */
    public function has(string|array $items): bool
    {
        if (is_array($items))
        {
            foreach ($items as $item)
            {
                if (!array_key_exists($item, $this->items))
                {
                    return false;
                }
            }

            return true;
        }

        return array_key_exists($items, $this->items);
    }

    /**
     * Alias for offsetGet
     * @inheritDoc
     */
    public function getItem(mixed $item): mixed
    {
        return $this->offsetGet($item);
    }

    /**
     * Alias for offsetSet
     * @inheritDoc
     */
    public function setItem(mixed $item, mixed $value): void
    {
        $this->offsetSet($item, $value);
    }

    /**
     * Alias for OffsetUnset
     * @inheritDoc
     */
    public function unsetItem(mixed $item): void
    {
        $this->offsetUnset($item);
    }

    /**
     * @inheritDoc
     */
    public function findIn(string $item, Closure $callback): mixed
    {
        $items = $this->getItem($item);

        if (!is_array($items))
        {
            return null;
        }

        foreach ($items as $entry)
        {
            if ($callback($entry))
            {
                return $entry;
            }
        }

        return null;
    }
}
