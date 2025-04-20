<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

class ColumnDefinitionBuilder
{
    protected SchemaBuilder $builder;
    protected string $name;
    protected string $type;
    protected bool $notNull = true;
    protected bool $isNullable = false;
    protected string|int|null $default = null;
    protected bool $isUnique = false;

    public function __construct(SchemaBuilder $builder, string $name, string $type)
    {
        $this->builder = $builder;
        $this->name = $name;
        $this->type = $type;
    }

    public function nullable(): self
    {
        $this->notNull = false;
        $this->isNullable = true;

        return $this;
    }

    public function notNull(): self
    {
        $this->notNull = true;
        $this->isNullable = false;

        return $this;
    }

    public function default(string|int|null $value): self
    {
        $this->default = $value;

        return $this;
    }

    public function unique(): self
    {
        $this->isUnique = true;

        return $this;
    }

    public function build(): void
    {
        $sql = '`' . $this->name . '` ' . $this->type;
        $sql .= $this->notNull ? ' NOT NULL' : ' NULL';
        if ($this->default !== null)
        {
            $sql .= ' DEFAULT \'' . addslashes((string)$this->default) . '\'';
        }

        $this->builder->addColumn($sql);

        if ($this->isUnique)
        {
            $index = "UNIQUE INDEX `unique_{$this->name}` (`{$this->name}`)";
            $this->builder->addIndex($index);
        }
    }

    public function __destruct()
    {
        $this->build();
    }
}
