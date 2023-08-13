<?php

declare(strict_types=1);

namespace Asterios\Core\Db\ORM\Support\Collections;

class Collection extends \ArrayObject
{
    /**
     * @param mixed $index
     * @param mixed $value
     * @return void
     */
    public function offsetSet($index, $value): void
    {
        parent::offsetSet($index, $value);
    }
}