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

    public function id(string $name = 'id'): ColumnDefinitionBuilder
    {
        return $this->column($name, 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY');
    }

    public function string(string $name, int $length = 255): ColumnDefinitionBuilder
    {
        return $this->column($name, "VARCHAR($length)");
    }

    public function int(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        $type = $unsigned ? 'INT UNSIGNED' : 'INT';

        return $this->column($name, $type);
    }

    public function smallInt(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        $type = $unsigned ? 'SMALLINT UNSIGNED' : 'SMALLINT';

        return $this->column($name, $type);
    }

    public function bigInt(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        $type = $unsigned ? 'BIGINT UNSIGNED' : 'BIGINT';

        return $this->column($name, $type);
    }

    public function boolean(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'TINYINT(1)');
    }

    public function enum(string $name, array $values): ColumnDefinitionBuilder
    {
        $quoted = array_map(static fn($v) => "'" . addslashes($v) . "'", $values);
        $type = 'ENUM(' . implode(', ', $quoted) . ')';

        return $this->column($name, $type);
    }

    public function text(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'TEXT');
    }

    public function mediumText(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'MEDIUMTEXT');
    }

    public function json(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'JSON');
    }

    public function char(string $name, int $length = 1): ColumnDefinitionBuilder
    {
        return $this->column($name, "CHAR($length)");
    }

    public function dateTime(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'DATETIME');
    }

    public function timestamps(): self
    {
        $this->columns[] = '`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
        $this->columns[] = '`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';

        return $this;
    }

    public function column(string $name, string $type): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, $name, $type);
    }

    public function addColumn(string $sql): void
    {
        $this->columns[] = $sql;
    }

    public function index(string|array $columns): IndexBuilder
    {
        return new IndexBuilder($this, $columns);
    }

    public function addIndex(string $sql): void
    {
        $this->indexes[] = $sql;
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
        return [$this->columns, $this->foreignKeys, $this->indexes];
    }
}
