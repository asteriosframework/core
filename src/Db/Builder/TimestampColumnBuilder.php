<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Interfaces\SupportsPrecisionInterface;

class TimestampColumnBuilder implements SupportsPrecisionInterface
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

    /**
     * @param int $value
     * @return $this
     */
    public function precision(int $value): static
    {
        $this->builder->setPrecision($this->column, $value);

        return $this;
    }
}
