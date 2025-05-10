<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Contracts\TimestampColumnBuilderInterface;

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

    /**
     * @inheritDoc
     */
    public function precision(int $value): self
    {
        $this->builder->setPrecision($this->column, $value);

        $this->builder->replaceColumnDefinition(
            $this->column,
            static function (string $definition) use ($value): string {
                return preg_replace(
                    '/TIMESTAMP(?:\(\d+\))?/',
                    'TIMESTAMP(' . $value . ')',
                    $definition
                );
            });

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function nullable(): self
    {
        $this->builder->replaceColumnDefinition(
            $this->column,
            static function (string $definition): string {
                if (!preg_match('/\bNULL\b/i', $definition))
                {
                    return preg_replace('/(TIMESTAMP(?:\(\d+\))?)/i', '$1 NULL', $definition);
                }

                return $definition;
            }
        );

        return $this;
    }
}
