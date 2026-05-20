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

    protected bool $alterMode = false;

    public function __construct(string $table, bool $alterMode = false)
    {
        $this->table = $table;
        $this->alterMode = $alterMode;
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
    public function uuid(string $name = 'uuid', string $mode = 'native'): ColumnDefinitionBuilder
    {
        $type = match (strtolower($mode))
        {
            'native' => 'UUID',
            'binary' => 'BINARY(16)',
            default => 'CHAR(36)',
        };

        $column = $this->column($name, $type)->unique();

        if (in_array($type, ['CHAR(36)', 'UUID'], true))
        {
            $column->default('UUID()', true);
        }

        return $column;
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
    public function char(string $name, int $length = 1): ColumnDefinitionBuilder
    {
        return $this->column($name, "CHAR($length)");
    }

    /**
     * @inheritDoc
     */
    public function email(string $name = 'email', int $length = 255): ColumnDefinitionBuilder
    {
        return $this->string($name, $length);
    }

    /**
     * @inheritDoc
     */
    public function url(string $name, int $length = 2048): ColumnDefinitionBuilder
    {
        return $this->string($name, $length);
    }

    /**
     * @inheritDoc
     */
    public function ipAddress(string $name): ColumnDefinitionBuilder
    {
        return $this->string($name, 45);
    }

    /**
     * @inheritDoc
     */
    public function macAddress(string $name): ColumnDefinitionBuilder
    {
        return $this->string($name, 17);
    }

    /**
     * @inheritDoc
     */
    public function phone(string $name, int $length = 32): ColumnDefinitionBuilder
    {
        return $this->string($name, $length);
    }

    /**
     * @inheritDoc
     */
    public function rememberToken(string $name = 'remember_token'): ColumnDefinitionBuilder
    {
        return $this->string($name, 100)->nullable();
    }

    /**
     * @inheritDoc
     */
    public function integer(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        return $this->column($name, $unsigned ? 'INT UNSIGNED' : 'INT');
    }

    public function tinyInteger(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        return $this->column($name, $unsigned ? 'TINYINT UNSIGNED' : 'TINYINT');
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
    public function mediumInteger(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        return $this->column($name, $unsigned ? 'MEDIUMINT UNSIGNED' : 'MEDIUMINT');
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
    public function float(string $name, int $precision = 10, int $scale = 2): ColumnDefinitionBuilder
    {
        return $this->column($name, 'FLOAT(' . $precision . ', ' . $scale . ')');
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
    public function set(string $name, array $values): ColumnDefinitionBuilder
    {
        $quoted = array_map(static fn ($v) => "'" . addslashes($v) . "'", $values);
        return $this->column($name, 'SET(' . implode(', ', $quoted) . ')');
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
    public function tinyText(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'TINYTEXT');
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
    public function longText(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'LONGTEXT');
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
    public function binary(string $name, int $length = 255): ColumnDefinitionBuilder
    {
        return $this->column($name, 'VARBINARY(' . $length . ')');
    }

    /**
     * @inheritDoc
     */
    public function blob(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'BLOB');
    }

    /**
     * @inheritDoc
     */
    public function mediumBlob(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'MEDIUMBLOB');
    }

    /**
     * @inheritDoc
     */
    public function longBlob(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'LONGBLOB');
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
    public function timestamp(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'TIMESTAMP');
    }

    /**
     * @inheritDoc
     */
    public function date(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'DATE');
    }

    /**
     * @inheritDoc
     */
    public function time(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'TIME');
    }

    /**
     * @inheritDoc
     */
    public function year(string $name): ColumnDefinitionBuilder
    {
        return $this->column($name, 'YEAR');
    }

    /**
     * @inheritDoc
     */
    public function fullText(string $name): ColumnDefinitionBuilder
    {
        $column = $this->text($name);
        $indexName = 'fulltext_index_' . $name;
        $this->addIndex('FULLTEXT INDEX `' . $indexName . '` (`' . $name . '`)');
        return $column;
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
        $this->columns[] = '`' . $column . '` TIMESTAMP' . $precision . ' DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP' . $precision;
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
        $this->columns[] = '`' . $column . '` TIMESTAMP' . $precision . ' NULL DEFAULT NULL';
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
    public function isAlterMode(): bool
    {
        return $this->alterMode;
    }

    /**
     * @inheritDoc
     */
    public function dropColumn(string|array $columns): void
    {
        foreach ((array)$columns as $column)
        {
            $this->columns[] = 'DROP COLUMN `' . $column . '`';
        }
    }

    /**
     * @inheritDoc
     */
    public function dropIndex(string $name): void
    {
        $this->indexes[] = 'DROP INDEX `' . $name . '`';
    }

    /**
     * @inheritDoc
     */
    public function dropForeign(string $name): void
    {
        $this->foreignKeys[] = 'DROP FOREIGN KEY `' . $name . '`';
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
