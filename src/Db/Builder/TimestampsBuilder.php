<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Interfaces\SupportsPrecisionInterface;

class TimestampsBuilder implements SupportsPrecisionInterface
{
    protected SchemaBuilder $builder;
    protected string $createdAt;
    protected string $updatedAt;

    public function __construct(SchemaBuilder $builder, string $createdAt, string $updatedAt)
    {
        $this->builder = $builder;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function precision(int $value): static
    {
        $this->builder->setPrecision($this->createdAt, $value);
        $this->builder->setPrecision($this->updatedAt, $value);

        return $this;
    }

    public function __call(string $method, array $arguments)
    {
        return $this->builder->{$method}(...$arguments);
    }
}