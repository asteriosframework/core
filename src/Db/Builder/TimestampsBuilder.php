<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

use Asterios\Core\Contracts\TimestampsBuilderInterface;

class TimestampsBuilder implements TimestampsBuilderInterface
{
    protected SchemaBuilder $schema;
    protected string $createdAt;
    protected string $updatedAt;
    protected ?int $precision = null;
    protected bool $isNullable = false;

    public function __construct(SchemaBuilder $schema, string $createdAt = 'created_at', string $updatedAt = 'updated_at')
    {
        $this->schema = $schema;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;

        $this->applyColumns();
    }

    /**
     * @inheritDoc
     */
    public function precision(int $value): self
    {
        $this->precision = $value;

        $this->applyColumns();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function nullable(): self
    {
        $this->isNullable = true;

        $this->applyColumns();

        return $this;
    }

    protected function applyColumns(): void
    {
        $this->schema->setPrecision($this->createdAt, $this->precision ?? 0);
        $this->schema->setPrecision($this->updatedAt, $this->precision ?? 0);

        $this->schema->removeTimestampColumns($this->createdAt, $this->updatedAt);

        $this->schema->addColumn(sprintf(
            '`%s` TIMESTAMP%s %s',
            $this->createdAt,
            $this->getPrecisionString($this->createdAt),
            $this->isNullable
                ? 'NULL DEFAULT NULL'
                : 'DEFAULT CURRENT_TIMESTAMP' . $this->getPrecisionString($this->createdAt)
        ));

        $this->schema->addColumn(sprintf(
            '`%s` TIMESTAMP%s %s',
            $this->updatedAt,
            $this->getPrecisionString($this->updatedAt),
            $this->isNullable
                ? 'NULL DEFAULT NULL'
                : 'DEFAULT CURRENT_TIMESTAMP' . $this->getPrecisionString($this->updatedAt) . ' ON UPDATE CURRENT_TIMESTAMP' . $this->getPrecisionString($this->updatedAt)
        ));
    }

    protected function getPrecisionString(string $column): string
    {
        $value = $this->precision ?? $this->schema->getTimestampPrecision($column);

        return $value > 0 ? "($value)" : '';
    }
}
