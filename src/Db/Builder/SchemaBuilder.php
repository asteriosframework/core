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
        $sqlDataType = (true === $bigInt ? 'BIGINT' : 'INT');
        $sqlAutoIncrement = (true === $autoIncrement) ? 'AUTO_INCREMENT' : '';
        $sqlUnsigned = (true === $unsigned) ? ' UNSIGNED' : '';

        $this->columns[] = '`' . $columnName . '` ' . $sqlDataType . ' ' . $sqlUnsigned . ' ' . $sqlAutoIncrement . ' PRIMARY KEY';

        return $this;
    }

    public function smallInt(string $columnName, bool $unsigned = true, bool $notNull = true, string|int|null $default = null): void
    {
        $type = $unsigned ? 'SMALLINT UNSIGNED' : 'INT';
        $this->columns[] = '`' . $columnName . '` ' . $type . $this->setNotNull($notNull) . $this->setDefault($default);
    }

    public function int(string $columnName, bool $unsigned = true, bool $notNull = true, string|int|null $default = null): void
    {
        $type = $unsigned ? 'INT UNSIGNED' : 'INT';
        $this->columns[] = '`' . $columnName . '` ' . $type . $this->setNotNull($notNull) . $this->setDefault($default);
    }

    public function bigInt(string $columnName, bool $unsigned = true, bool $notNull = true, string|int|null $default = null): self
    {
        $sqlUnsigned = (true === $unsigned) ? ' UNSIGNED' : '';
        $this->columns[] = '`' . $columnName . '` BIGINT' . $sqlUnsigned . $this->setNotNull($notNull) . $this->setDefault($default);

        return $this;
    }

    public function varChar(string $columnName, int $length = 255, bool $notNull = true, string|int|null $default = null): self
    {
        $this->columns[] = '`' . $columnName . '` VARCHAR(' . $length . ')' . $this->setNotNull($notNull) . $this->setDefault($default);

        return $this;
    }

    public function boolean(string $columnName, bool $notNull = true, string|int|null $default = null): void
    {
        $this->columns[] = '`' . $columnName . '` TINYINT(1)' . $this->setNotNull($notNull) . $this->setDefault($default);
    }

    public function enum(string $columnName, array $values, string|int|null $default = null, bool $notNull = true): self
    {
        $quotedValues = array_map(static fn($val) => "'" . addslashes($val) . "'", $values);
        $enumValues = implode(', ', $quotedValues);

        $this->columns[] = '`' . $columnName . '` ENUM(' . $enumValues . ')' . $this->setNotNull($notNull) . $this->setDefault($default);

        return $this;
    }

    public function timestamps(): self
    {
        $this->columns[] = '`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
        $this->columns[] = '`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';

        return $this;
    }

    public function index(string|array $columns): IndexBuilder
    {
        return new IndexBuilder($this, $columns);
    }

    public function addIndex(string $sql): void
    {
        $this->indexes[] = $sql;
    }

    public function foreign(string $columnName): ForeignKeyBuilder
    {
        return new ForeignKeyBuilder($this, $columnName);
    }

    public function addForeignKey(string $sql): void
    {
        $this->foreignKeys[] = $sql;
    }

    public function build(): array
    {
        return [$this->columns, $this->foreignKeys, $this->indexes];
    }

    protected function setNotNull(bool $setNotNull = true): string
    {
        return (true === $setNotNull) ? ' NOT NULL' : ' NULL';
    }

    protected function setDefault(string|int|null $value): string
    {
        return (null !== $value) ? ' DEFAULT \'' . $value . '\'' : '';
    }
}