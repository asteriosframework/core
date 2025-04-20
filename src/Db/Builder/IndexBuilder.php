<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

class IndexBuilder
{
    protected SchemaBuilder $builder;
    protected array|string $columns;
    protected bool $unique = false;

    public function __construct(SchemaBuilder $builder, $columns)
    {
        $this->builder = $builder;
        $this->columns = is_array($columns) ? $columns : [$columns];
    }

    public function unique(): self
    {
        $this->unique = true;

        return $this;
    }

    public function add(): void
    {
        $columnsSql = implode('`, `', $this->columns);

        $indexName = 'index_' . implode('_', $this->columns);
        $indexName = preg_replace('/[^a-zA-Z0-9_]/', '_', $indexName);

        $type = $this->unique ? 'UNIQUE' : 'INDEX';
        
        $this->builder->addIndex("{$type} `{$indexName}` (`{$columnsSql}`)");
    }
}