<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Contracts\ColumnDefinitionBuilderInterface;
use Asterios\Core\Db;
use Asterios\Core\Exception\ConfigLoadException;

class ColumnDefinitionBuilder implements ColumnDefinitionBuilderInterface
{
    protected SchemaBuilder $builder;
    protected string $name;
    protected string $type;
    protected bool $notNull = true;
    protected bool $isNullable = false;
    protected string|int|null|object $default = null; // kann jetzt auch Funktionsausdruck sein
    protected bool $isUnique = false;

    public function __construct(SchemaBuilder $builder, string $name, string $type)
    {
        $this->builder = $builder;
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function nullable(): self
    {
        $this->notNull = false;
        $this->isNullable = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function notNull(): self
    {
        $this->notNull = true;
        $this->isNullable = false;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function default(string|int|null $value, bool $isExpression = false): self
    {
        if ($value === null)
        {
            $this->default = null;
        }
        elseif ($isExpression)
        {
            // SQL-Funktion
            $obj = new \stdClass();
            $obj->expr = $value;
            $this->default = $obj;
        }
        else
        {
            // Literalwert
            $this->default = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unique(): self
    {
        $this->isUnique = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function build(): void
    {
        $type = strtoupper($this->type);

        $sqlType = match (true)
        {
            $type === 'UUID' && Db::isMariaDb() => 'UUID',
            $type === 'UUID' => 'CHAR(36)',
            default => $this->type,
        };

        $sql = '`' . $this->name . '` ' . $sqlType;
        $sql .= $this->notNull ? ' NOT NULL' : ' NULL';

        if ($this->default !== null)
        {
            if (is_object($this->default) && isset($this->default->expr))
            {
                $sql .= ' DEFAULT ' . $this->default->expr;
            }
            else
            {
                $sql .= ' DEFAULT \'' . addslashes((string)$this->default) . '\'';
            }
        }

        $this->builder->addColumn($sql);

        if ($this->isUnique)
        {
            $index = "UNIQUE INDEX `unique_{$this->name}` (`{$this->name}`)";
            $this->builder->addIndex($index);
        }
    }

    /**
     * @throws ConfigLoadException
     */
    public function __destruct()
    {
        $this->build();
    }
}