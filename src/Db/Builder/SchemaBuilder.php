<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Interfaces\SchemaBuilderInterface;

class SchemaBuilder implements SchemaBuilderInterface
{
    protected string $table;
    protected array $columns = [];
    protected array $foreignKeys = [];
    protected array $indexes = [];

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
        return $this->column($name, 'VARCHAR(' . $length . ')');
    }

    /**
     * @inheritDoc
     */
    public function int(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        $type = $unsigned ? 'INT UNSIGNED' : 'INT';

        return $this->column($name, $type);
    }

    /**
     * @inheritDoc
     */
    public function smallInt(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        $type = $unsigned ? 'SMALLINT UNSIGNED' : 'SMALLINT';

        return $this->column($name, $type);
    }

    /**
     * @inheritDoc
     */
    public function bigInt(string $name, bool $unsigned = true): ColumnDefinitionBuilder
    {
        $type = $unsigned ? 'BIGINT UNSIGNED' : 'BIGINT';

        return $this->column($name, $type);
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
        $quoted = array_map(static fn($v) => "'" . addslashes($v) . "'", $values);
        $type = 'ENUM(' . implode(', ', $quoted) . ')';

        return $this->column($name, $type);
    }

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
    public function timestamps(string $createdAt = 'created_at', string $updatedAt = 'updated_at'): self
    {
        $this->columns[] = "`{$createdAt}` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "`{$updatedAt}` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";

        return $this;
    }

    public function softDeletes(string $column = 'deleted_at'): self
    {
        $this->columns[] = "`{$column}` TIMESTAMP NULL DEFAULT NULL";

        return $this;
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
    public function build(): array
    {
        return [$this->columns, $this->foreignKeys, $this->indexes];
    }
}
