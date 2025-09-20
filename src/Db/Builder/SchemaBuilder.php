<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Contracts\SchemaBuilderInterface;

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
    public function uuid(string $name = 'uuid', bool $native = true): ColumnDefinitionBuilder
    {
        $type = $native ? 'UUID' : 'CHAR(36)';
        return $this->column($name, $type);
    }

    /**
     * @inheritDoc
     */
    public function string(string $name, int $length = 255): ColumnDefinitionBuilder
    {
        return $this->column($name, "VARCHAR($length)");
    }

    /**
     * @inheritDoc
     */
    public function integer(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        return $this->column($name, $unsigned ? 'INT UNSIGNED' : 'INT');
    }

    /**
     * @inheritDoc
     */
    public function smallInteger(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        return $this->column($name, $unsigned ? 'SMALLINT UNSIGNED' : 'SMALLINT');
    }

    /**
     * @inheritDoc
     */
    public function bigInteger(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        return $this->column($name, $unsigned ? 'BIGINT UNSIGNED' : 'BIGINT');
    }

    /**
     * @inheritDoc
     */
    public function boolean(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'TINYINT(1)');
    }

    /**
     * @inheritDoc
     */
    public function enum(string $name, array $values): ColumnDefinitionBuilder
    {
        $quoted = array_map(static fn ($v) => "'" . addslashes($v) . "'", $values);

        return $this->column($name, 'ENUM(' . implode(', ', $quoted) . ')');
    }

    /**
     * @inheritDoc
     */
    public function text(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'TEXT');
    }

    /**
     * @inheritDoc
     */
    public function mediumText(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'MEDIUMTEXT');
    }

    /**
     * @inheritDoc
     */
    public function json(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'JSON');
    }

    /**
     * @inheritDoc
     */
    public function char(string $name, int $length = 1): ColumnDefinitionBuilder
    {
        return $this->column($name, "CHAR($length)");
    }

    /**
     * @inheritDoc
     */
    public function dateTime(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'DATETIME');
    }

    /**
     * @inheritDoc
     */
    public function timestamps(string $createdAt = 'created_at', string $updatedAt = 'updated_at'): TimestampsBuilder
    {
        return new TimestampsBuilder($this, $createdAt, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function createdAt(string $column = 'created_at'): TimestampColumnBuilder
    {
        $precision = $this->getPrecision($column);
        $this->columns[] = '`' . $column . '` TIMESTAMP' . $precision . ' DEFAULT CURRENT_TIMESTAMP' . $precision;

        return new TimestampColumnBuilder($this, $column);
    }

    /**
     * @inheritDoc
     */
    public function updatedAt(string $column = 'updated_at'): TimestampColumnBuilder
    {
        $precision = $this->getPrecision($column);
        $this->columns[] = '`' . $column . '` TIMESTAMP' . $precision . ' ON UPDATE CURRENT_TIMESTAMP' . $precision;

        return new TimestampColumnBuilder($this, $column);
    }

    /**
     * @inheritDoc
     */
    public function deletedAt(string $column = 'deleted_at'): TimestampColumnBuilder
    {
        $precision = $this->getPrecision($column);
        $this->columns[] = '`' . $column . '` TIMESTAMP' . $precision . ' NULL DEFAULT NULL';

        return new TimestampColumnBuilder($this, $column);
    }

    /**
     * @inheritDoc
     */
    public function softDeletes(string $column = 'deleted_at'): TimestampColumnBuilder
    {
        $precision = $this->getPrecision($column);
        $this->columns[] = '`' . $column . '` TIMESTAMP' . $precision . ' NULL DEFAULT NULL AFTER `updated_at`';

        return new TimestampColumnBuilder($this, $column);
    }

    /**
     * @inheritDoc
     */
    public function removeTimestampColumns(string ...$columns): void
    {
        $this->columns = array_filter($this->columns, static function (string $col) use ($columns) {
            foreach ($columns as $name)
            {
                if (str_contains($col, "`$name`"))
                {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * @inheritDoc
     */
    public function getTimestampPrecision(string $column): int
    {
        return $this->timestampPrecisions[$column] ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function replaceColumnDefinition(string $column, callable $callback): void
    {
        foreach ($this->columns as $i => $definition)
        {
            if (str_starts_with($definition, "`$column`"))
            {
                $this->columns[$i] = $callback($definition);
                break;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function column(string $name, string $type): ColumnDefinitionBuilder
    {
        return new ColumnDefinitionBuilder($this, $name, $type);
    }

    /**
     * @inheritDoc
     */
    public function addColumn(string $sql): void
    {
        $this->columns[] = $sql;
    }

    /**
     * @inheritDoc
     */
    public function index(string|array $columns): IndexBuilder
    {
        return new IndexBuilder($this, $columns);
    }

    /**
     * @inheritDoc
     */
    public function addIndex(string $sql): void
    {
        $this->indexes[] = $sql;
    }

    /**
     * @inheritDoc
     */
    public function foreign(string $column): ForeignKeyBuilder
    {
        return new ForeignKeyBuilder($this, $column);
    }

    /**
     * @inheritDoc
     */
    public function addForeignKey(string $sql): void
    {
        $this->foreignKeys[] = $sql;
    }

    /**
     * @inheritDoc
     */
    public function precision(int $value, string $column = 'created_at'): self
    {
        $this->timestampPrecisions[$column] = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function build(): array
    {
        return [$this->columns, $this->foreignKeys, $this->indexes];
    }

    /**
     * @inheritDoc
     */
    public function setPrecision(string $column, int $precision): void
    {
        $this->timestampPrecisions[$column] = $precision;
    }

    /**
     * @inheritDoc
     */
    public function double(string $name, int $precision = 10, int $scale = 2): ColumnDefinitionBuilder
    {
        return $this->column($name, 'DOUBLE(' . $precision . ', ' . $scale . ')');
    }

    /**
     * @inheritDoc
     */
    public function decimal(string $name, int $precision = 10, int $scale = 2): ColumnDefinitionBuilder
    {
        return $this->column($name, 'DECIMAL(' . $precision . ', ' . $scale . ')');
    }

    /**
     * @param string $column
     * @return string
     */
    protected function getPrecision(string $column): string
    {
        return isset($this->timestampPrecisions[$column]) && $this->timestampPrecisions[$column] > 0
            ? '(' . $this->timestampPrecisions[$column] . ')'
            : '';
    }
}
