<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

class ColumnBuilder
{
    protected SchemaBuilder $builder;
    protected string $definition;

    public function __construct(SchemaBuilder $builder, string $definition)
    {
        $this->builder = $builder;
        $this->definition = $definition;
    }

    public function nullable(): self
    {
        $this->definition .= ' NULL';

        return $this;
    }

    public function notNullable(): self
    {
        $this->definition .= ' NOT NULL';

        return $this;
    }

    public function default(string|int|null $value): self
    {
        if ($value === null)
        {
            $this->definition .= ' DEFAULT NULL';
        }
        else
        {
            $this->definition .= " DEFAULT '" . addslashes((string)$value) . "'";
        }

        return $this;
    }

    public function primary(): self
    {
        $this->definition .= ' PRIMARY KEY';

        return $this;
    }

    public function autoIncrement(): self
    {
        $this->definition .= ' AUTO_INCREMENT';

        return $this;
    }

    public function unsigned(): self
    {
        $this->definition .= ' UNSIGNED';

        return $this;
    }

    public function done(): void
    {
        $this->builder->addColumn($this->definition);
    }

    public function __destruct()
    {
        $this->done();
    }
}