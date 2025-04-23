<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Interfaces\TimestampColumnBuilderInterface;

class TimestampColumnBuilder implements TimestampColumnBuilderInterface
{
    protected SchemaBuilder $builder;
    protected string $column;

    public function __construct(SchemaBuilder $builder, string $column)
    {
        $this->builder = $builder;
        $this->column = $column;
    }

    public function __call(string $method, array $arguments)
    {
        $result = $this->builder->{$method}(...$arguments);

        if ($result instanceof SchemaBuilder)
        {
            return new self($result, $this->column);
        }

        return $result;
    }

    public function precision(int $value): self
    {
        $this->builder->setPrecision($this->column, $value);

        return $this;
    }
}
