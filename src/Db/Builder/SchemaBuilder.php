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

    public function id(string $columnName = 'id'): ColumnDefinitionBuilder
    {
        return $this->bigInt($columnName)
            ->unsigned()
            ->autoIncrement()
            ->primary();
    }

    public function string(string $columnName, int $length = 255): ColumnDefinitionBuilder
    {
        return $this->varChar($columnName, $length);
    }

    public function varChar(string $columnName, int $length = 255): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, "`{$columnName}` VARCHAR({$length})");
    }

    public function char(string $columnName, int $length = 1): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, "`{$columnName}` CHAR({$length})");
    }

    public function text(string $columnName): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, "`{$columnName}` TEXT");
    }

    public function mediumText(string $columnName): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, "`{$columnName}` MEDIUMTEXT");
    }

    public function json(string $columnName): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, "`{$columnName}` JSON");
    }

    public function int(string $columnName): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, "`{$columnName}` INT");
    }

    public function bigInt(string $columnName): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, "`{$columnName}` BIGINT");
    }

    public function smallInt(string $columnName): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, "`{$columnName}` SMALLINT");
    }

    public function boolean(string $columnName): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, "`{$columnName}` TINYINT(1)");
    }

    public function dateTime(string $columnName): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, "`{$columnName}` DATETIME");
    }

    public function enum(string $columnName, array $values): ColumnDefinitionBuilder
    {
        $quoted = array_map(static fn($v) => "'" . addslashes($v) . "'", $values);

        return new ColumnDefinitionBuilder($this, "`{$columnName}` ENUM(" . implode(', ', $quoted) . ")");
    }

    public function timestamps(): void
    {
        $this->columns[] = '`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
        $this->columns[] = '`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
    }

    public function foreign(string $column): ForeignKeyBuilder
    {
        return new ForeignKeyBuilder($this, $column);
    }

    public function index(string|array $columns): IndexBuilder
    {
        return new IndexBuilder($this, $columns);
    }

    public function addColumn(string $definition): void
    {
        $this->columns[] = $definition;
    }

    public function addIndex(string $definition): void
    {
        $this->indexes[] = $definition;
    }

    public function addForeignKey(string $definition): void
    {
        $this->foreignKeys[] = $definition;
    }

    public function build(): array
    {
        return [$this->columns, $this->foreignKeys, $this->indexes];
    }
}
