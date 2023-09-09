<?php

declare(strict_types=1);

namespace Asterios\Core\Db\ORM\Support\Collections;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends \ArrayObject<TKey, TValue>
 */
class Collection extends \ArrayObject
{
    /**
     * @param TKey $index
     * @param TValue $value
     * @return void
     */
    public function offsetSet(mixed $index, mixed $value): void
    {
        parent::offsetSet($index, $value);
    }
}