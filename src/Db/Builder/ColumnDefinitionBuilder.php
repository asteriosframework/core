<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

class ColumnDefinitionBuilder
{
    protected string $name;
    protected ?string $type = null;
    protected array $modifiers = [];
    protected bool $isFinalized = false;

    public function __construct(protected SchemaBuilder $builder, string $name)
    {
        $this->name = $name;
    }

    public function int(bool $unsigned = true): void
    {
        $this->type = $unsigned ? 'INT UNSIGNED' : 'INT';
        $this->finalize();
    }

    public function bigInt(bool $unsigned = true): void
    {
        $this->type = $unsigned ? 'BIGINT UNSIGNED' : 'BIGINT';
        $this->finalize();
    }

    public function smallInt(bool $unsigned = true): void
    {
        $this->type = $unsigned ? 'SMALLINT UNSIGNED' : 'SMALLINT';
        $this->finalize();
    }

    public function varChar(int $length = 255): void
    {
        $this->type = 'VARCHAR(' . $length . ')';
        $this->finalize();
    }

    public function char(int $length = 1): void
    {
        $this->type = 'CHAR(' . $length . ')';
        $this->finalize();
    }

    public function text(): void
    {
        $this->type = 'TEXT';
        $this->finalize();
    }

    public function mediumText(): void
    {
        $this->type = 'MEDIUMTEXT';
        $this->finalize();
    }

    public function json(): void
    {
        $this->type = 'JSON';
        $this->finalize();
    }

    public function dateTime(): void
    {
        $this->type = 'DATETIME';
        $this->finalize();
    }

    public function boolean(): void
    {
        $this->type = 'TINYINT(1)';
        $this->finalize();
    }

    public function enum(array $values): void
    {
        $quoted = array_map(static fn($v) => "'" . addslashes($v) . "'", $values);
        $this->type = 'ENUM(' . implode(', ', $quoted) . ')';
        $this->finalize();
    }

    public function nullable(): self
    {
        $this->modifiers[] = 'NULL';

        return $this;
    }

    public function notNull(): self
    {
        $this->modifiers[] = 'NOT NULL';

        return $this;
    }

    public function default(string|int|float|null $value): self
    {
        if ($value === null)
        {
            $this->modifiers[] = 'DEFAULT NULL';
        }
        else
        {
            $this->modifiers[] = 'DEFAULT \'' . addslashes((string)$value) . '\'';
        }

        return $this;
    }

    public function primary(): self
    {
        $this->modifiers[] = 'PRIMARY KEY';

        return $this;
    }

    public function autoIncrement(): self
    {
        $this->modifiers[] = 'AUTO_INCREMENT';

        return $this;
    }

    public function unsigned(): self
    {
        if ($this->type !== null)
        {
            $this->type = str_replace(['INT', 'BIGINT', 'SMALLINT'], ['INT UNSIGNED', 'BIGINT UNSIGNED', 'SMALLINT UNSIGNED'], $this->type);
        }

        return $this;
    }

    public function unique(): self
    {
        $this->builder->addIndex("UNIQUE INDEX `unique_{$this->name}` (`{$this->name}`)");

        return $this;
    }

    protected function finalize(): void
    {
        if ($this->isFinalized)
        {
            return;
        }

        $definition = '`' . $this->name . '` ' . $this->type;

        if (!empty($this->modifiers))
        {
            $definition .= ' ' . implode(' ', $this->modifiers);
        }

        $this->builder->addColumn(trim($definition));
        $this->isFinalized = true;
    }
}
