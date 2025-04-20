<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

class IndexBuilder
{
    protected SchemaBuilder $builder;
    protected array $columns;
    protected bool $isUnique = false;

    public function __construct(SchemaBuilder $builder, string|array $columns)
    {
        $this->builder = $builder;
        $this->columns = (array)$columns;
    }

    public function unique(): self
    {
        $this->isUnique = true;

        return $this;
    }

    public function add(): void
    {
        $indexName = ($this->isUnique ? 'unique index_' : 'index_') . implode('_', $this->columns);
        $cols = implode(', ', array_map(static fn($col) => "`$col`", $this->columns));
        $index = ($this->isUnique ? 'UNIQUE ' : '') . "INDEX `$indexName` ($cols)";
        $this->builder->addIndex($index);
    }
}
