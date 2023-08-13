<?php

declare(strict_types=1);

namespace Asterios\Core\Db\ORM\Support\Collections;

class ResultCollection extends Collection
{
    /**
     * @param mixed $index
     * @param mixed $value
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