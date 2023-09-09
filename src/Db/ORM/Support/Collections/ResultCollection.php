<?php

declare(strict_types=1);

namespace Asterios\Core\Db\ORM\Support\Collections;

/**
 * @template TKey of array-key
 * @template TValue of array|object
 *
 * @extends Collection<TKey, TValue>
 */
class ResultCollection extends Collection
{
    /**
     * @param TKey $index
     * @param TValue $value
     * @return void
     */
    public function offsetSet($index, $value): void
    {
        if (!is_array($value) && !is_object($value))
        {
            throw new \InvalidArgumentException('Must be an array or object');
        }

        parent::offsetSet($index, $value);
    }
}