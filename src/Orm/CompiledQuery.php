<?php

declare(strict_types=1);

namespace Asterios\Core\Orm;

use Asterios\Core\Data;

final class CompiledQuery extends Data
{
    public string $sql = '';

    /** @var list<int|float|string|bool|null> */
    public array $bindings = [];

    public bool $prepared = true;

    public function __construct(
        string $sql = '',
        array $bindings = []
    ) {
        $this->sql = $sql;
        $this->bindings = $bindings;
    }

    public function hasBindings(): bool
    {
        return $this->bindings !== [];
    }
}
