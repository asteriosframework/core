<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Interfaces\SchemaBuilderInterface;

class SchemaBuilder implements SchemaBuilderInterface
{
    protected string $table;
    protected array $columns = [];
    protected array $foreignKeys = [];
    protected array $indexes = [];
    protected array $timestampPrecisions = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * @inheritDoc
     */
    public function id(string $name = 'id'): ColumnDefinitionBuilder
    {
        return $this->column($name, 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY');
    }

    /**
     * @inheritDoc
     */
    public function string(string $name, int $length = 255): ColumnDefinitionBuilder
    {
        return $this->column($name, "VARCHAR($length)");
    }

    public function int(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        return $this->column($name, $unsigned ? 'INT UNSIGNED' : 'INT');
    }

    public function smallInt(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        return $this->column($name, $unsigned ? 'SMALLINT UNSIGNED' : 'SMALLINT');
    }

    public function bigInt(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        return $this->column($name, $unsigned ? 'BIGINT UNSIGNED' : 'BIGINT');
    }

    public function boolean(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'TINYINT(1)');
    }

    public function enum(string $name, array $values): ColumnDefinitionBuilder
    {
        $quoted = array_map(static fn($v) => "'" . addslashes($v) . "'", $values);

        return $this->column($name, 'ENUM(' . implode(', ', $quoted) . ')');
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

    /**
     * @inheritDoc
     */
    public function timestamps(string $createdAt = 'created_at', string $updatedAt = 'updated_at'): self
    {
        $this->createdAt($createdAt);
        $this->updatedAt($updatedAt);

        return $this;
    }

    public function createdAt(string $column = 'created_at'): TimestampColumnBuilder
    {
        $precision = $this->getPrecision($column);
        $this->columns[] = '`' . $column . '` TIMESTAMP' . $precision . ' DEFAULT CURRENT_TIMESTAMP' . $precision;

        return new TimestampColumnBuilder($this, $column);
    }

    public function updatedAt(string $column = 'updated_at'): TimestampColumnBuilder
    {
        $precision = $this->getPrecision($column);
        $this->columns[] = '`' . $column . '` TIMESTAMP' . $precision . ' DEFAULT CURRENT_TIMESTAMP' . $precision . ' ON UPDATE CURRENT_TIMESTAMP' . $precision;

        return new TimestampColumnBuilder($this, $column);
    }

    public function deletedAt(string $column = 'deleted_at'): TimestampColumnBuilder
    {
        $precision = $this->getPrecision($column);
        $this->columns[] = '`' . $column . '` TIMESTAMP' . $precision . ' NULL DEFAULT NULL';

        return new TimestampColumnBuilder($this, $column);
    }

    public function softDeletes(string $column = 'deleted_at'): TimestampColumnBuilder
    {
        $precision = $this->getPrecision($column);
        $this->columns[] = '`' . $column . '` TIMESTAMP' . $precision . ' NULL DEFAULT NULL AFTER `updated_at`';

        return new TimestampColumnBuilder($this, $column);
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

    public function setPrecision(string $column, int $precision): void
    {
        $this->timestampPrecisions[$column] = $precision;
    }

    protected function getPrecision(string $column): string
    {
        return isset($this->timestampPrecisions[$column]) && $this->timestampPrecisions[$column] > 0
            ? '(' . $this->timestampPrecisions[$column] . ')'
            : '';
    }
}
