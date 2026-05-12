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
    protected string|int|null|object $default = null;
    protected bool $isUnique = false;

    protected bool $change = false;

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
            $obj = new \stdClass();
            $obj->expr = $value;
            $this->default = $obj;
        }
        else
        {
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

        $definition = '`' . $this->name . '` ' . $sqlType;
        $definition .= $this->notNull ? ' NOT NULL' : ' NULL';

        if ($this->default !== null)
        {
            if (is_object($this->default) && isset($this->default->expr))
            {
                $definition .= ' DEFAULT ' . $this->default->expr;
            }
            else
            {
                $definition .= ' DEFAULT \'' . addslashes((string)$this->default) . '\'';
            }
        }

        if ($this->builder->isAlterMode())
        {
            $sql = $this->change
                ? 'MODIFY COLUMN ' . $definition
                : 'ADD COLUMN ' . $definition;
        }
        else
        {
            $sql = $definition;
        }

        $this->builder->addColumn($sql);

        if ($this->isUnique)
        {
            $index = "ADD UNIQUE INDEX `unique_{$this->name}` (`{$this->name}`)";

            if (!$this->builder->isAlterMode())
            {
                $index = "UNIQUE INDEX `unique_{$this->name}` (`{$this->name}`)";
            }

            $this->builder->addIndex($index);
        }
    }

    /**
     * @inheritDoc
     */
    public function change(): self
    {
        $this->change = true;
        return $this;
    }

    /**
     * @throws ConfigLoadException
     */
    public function __destruct()
    {
        $this->build();
    }
}
