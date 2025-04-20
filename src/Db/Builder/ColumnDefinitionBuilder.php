<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

class ColumnDefinitionBuilder
{
    protected string $columnName;
    protected string $columnType;
    protected string $constraints = '';

    public function __construct(string $columnName, string $columnType)
    {
        $this->columnName = $columnName;
        $this->columnType = $columnType;
    }

    public function nullable(): self
    {
        $this->constraints .= ' NULL';

        return $this;
    }

    public function notNull(): self
    {
        $this->constraints .= ' NOT NULL';

        return $this;
    }

    public function default(string|int|null $value): self
    {
        if ($value !== null)
        {
            $this->constraints .= " DEFAULT '$value'";
        }

        return $this;
    }

    public function unique(): self
    {
        $this->constraints .= ' UNIQUE';

        return $this;
    }

    public function build(): string
    {
        return "`$this->columnName` $this->columnType $this->constraints";
    }
}
