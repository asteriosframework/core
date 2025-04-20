<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

class SchemaBuilder
{
    protected string $table;
    protected array $columns = [];
    protected array $foreignKeys = [];
    protected array $indexes = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(string $columnName = 'id', bool $autoIncrement = true, bool $bigInt = true, bool $unsigned = true): self
    {
        $columnType = $bigInt ? 'BIGINT UNSIGNED' : 'INT UNSIGNED';
        $column = new ColumnDefinitionBuilder($columnName, $columnType);
        $column->notNull()
            ->default(null); // Typically, ID is not nullable, and it defaults to null.
        $this->columns[] = $column->build() . ' AUTO_INCREMENT PRIMARY KEY';

        return $this;
    }

    public function string(string $columnName, int $length = 255): self
    {
        $column = new ColumnDefinitionBuilder($columnName, "VARCHAR($length)");
        $this->columns[] = $column->build();

        return $this;
    }

    public function smallInt(string $columnName): self
    {
        $column = new ColumnDefinitionBuilder($columnName, 'SMALLINT');
        $this->columns[] = $column->build();

        return $this;
    }

    public function enum(string $columnName, array $values, string|int|null $default = null): self
    {
        $enumValues = implode("', '", $values);
        $column = new ColumnDefinitionBuilder($columnName, "ENUM('$enumValues')");
        $this->columns[] = $column->default($default)
            ->build();

        return $this;
    }

    public function int(string $columnName): self
    {
        $column = new ColumnDefinitionBuilder($columnName, 'INT');
        $this->columns[] = $column->build();

        return $this;
    }

    public function timestamps(): self
    {
        $this->columns[] = "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";

        return $this;
    }

    public function build(): array
    {
        return [
            'columns' => $this->columns, // Alle Spalten
            'foreignKeys' => $this->foreignKeys,
            'indexes' => $this->indexes,
        ];
    }
}
