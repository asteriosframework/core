<?php

declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

class ForeignKeyBuilder
{
    protected string $column;
    protected string $references;
    protected string $on;
    protected string $onDelete = '';
    protected string $onUpdate = '';

    protected SchemaBuilder $builder;

    public function __construct(SchemaBuilder $builder, string $column)
    {
        $this->builder = $builder;
        $this->column = $column;
    }

    public function references(string $ref): self
    {
        $this->references = $ref;

        return $this;
    }

    public function on(string $table): self
    {
        $this->on = $table;

        return $this;
    }

    public function onUpdate(string $action = 'CASCADE'): self
    {
        $this->onDelete = 'ON UPDATE ' . strtoupper($action);

        return $this;
    }

    public function onDelete(string $action = 'CASCADE'): self
    {
        $this->onDelete = 'ON DELETE ' . strtoupper($action);
        $this->finalize();

        return $this;
    }

    protected function finalize(): void
    {
        $fkName = "fk_{$this->column}";
        $sql = "CONSTRAINT `{$fkName}` FOREIGN KEY (`{$this->column}`) REFERENCES `{$this->on}`(`{$this->references}`) {$this->onDelete}";
        $this->builder->addForeignKey($sql);
    }
}
