<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Contracts\IndexBuilderInterface;

class IndexBuilder implements IndexBuilderInterface
{
    protected SchemaBuilder $builder;
    protected array $columns;
    protected bool $isUnique = false;
    protected bool $isFullText = false;


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
    public function fullText(): self
    {
        $this->isFullText = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function add(): void
    {
        $prefix = match (true) {
            $this->isFullText => 'FULLTEXT ',
            $this->isUnique   => 'UNIQUE ',
            default           => '',
        };

        $indexNamePrefix = match (true) {
            $this->isFullText => 'fulltext_index_',
            $this->isUnique   => 'unique_index_',
            default           => 'index_',
        };

        $indexName = $indexNamePrefix . implode('_', $this->columns);

        $cols = implode(', ', array_map(
            static fn ($col) => "`$col`",
            $this->columns
        ));

        $index = $prefix . "INDEX `$indexName` ($cols)";

        $this->builder->addIndex($index);
    }
}
