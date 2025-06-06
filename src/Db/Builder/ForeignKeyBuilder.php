<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Contracts\ForeignKeyBuilderInterface;

class ForeignKeyBuilder implements ForeignKeyBuilderInterface
{
    protected SchemaBuilder $builder;
    protected string $column;
    protected string $referenceTable = '';
    protected string $referenceColumn = 'id';
    protected string $onDelete = '';
    protected string $onUpdate = '';

    public function __construct(SchemaBuilder $builder, string $column)
    {
        $this->builder = $builder;
        $this->column = $column;
    }

    /**
     * @inheritDoc
     */
    public function references(string $table, string $column = 'id'): self
    {
        $this->referenceTable = $table;
        $this->referenceColumn = $column;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function onDelete(string $action): self
    {
        $this->onDelete = $action;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function onUpdate(string $action): self
    {
        $this->onUpdate = $action;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function add(): void
    {
        $sql = "FOREIGN KEY (`{$this->column}`) REFERENCES `{$this->referenceTable}`(`{$this->referenceColumn}`)";
        if ($this->onDelete)
        {
            $sql .= " ON DELETE {$this->onDelete}";
        }
        if ($this->onUpdate)
        {
            $sql .= " ON UPDATE {$this->onUpdate}";
        }

        $this->builder->addForeignKey($sql);
    }
}
