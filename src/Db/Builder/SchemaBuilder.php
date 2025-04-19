<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

class SchemaBuilder
{
    protected string $table;
    protected array $columns = [];
    protected array $foreignKeys = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(string $name = 'id'): self
    {
        $this->columns[] = '`' . $name . '` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY';

        return $this;
    }

    public function bigInt(string $name, $unsigned = true): self
    {
        $sqlUnsigned = (true === $unsigned) ? ' UNSIGNED' : '';
        $this->columns[] = '`' . $name . '` BIGINT' . $sqlUnsigned;

        return $this;
    }

    public function string(string $name, int $length = 255): self
    {
        $this->columns[] = "`$name` VARCHAR(\$length)";

        return $this;
    }

    public function foreign(string $column): ForeignKeyBuilder
    {
        return new ForeignKeyBuilder($this, $column);
    }

    public function addForeignKey(string $sql): void
    {
        $this->foreignKeys[] = $sql;
    }

    public function build(): array
    {
        return [$this->columns, $this->foreignKeys];
    }
}