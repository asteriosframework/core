<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Contracts\IndexBuilderInterface;

class IndexBuilder implements IndexBuilderInterface
{
    protected SchemaBuilder $builder;
    protected array $columns;
    protected bool $isUnique = false;

    public function __construct(SchemaBuilder $builder, string|array $columns)
    {
        $this->builder = $builder;
        $this->columns = (array)$columns;
    }

    /**
     * @inheritDoc
     */
    public function unique(): self
    {
        $this->isUnique = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function add(): void
    {
        $indexName = ($this->isUnique ? 'unique index_' : 'index_') . implode('_', $this->columns);
        $cols = implode(', ', array_map(static fn($col) => "`$col`", $this->columns));
        $index = ($this->isUnique ? 'UNIQUE ' : '') . "INDEX `$indexName` ($cols)";
        $this->builder->addIndex($index);
    }
}
