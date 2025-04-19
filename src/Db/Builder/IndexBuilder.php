<?php declare(strict_types=1);

namespace Asterios\Core\Db\Builder;

class IndexBuilder
{
    protected string $type = 'INDEX';
    protected array $columns = [];
    protected ?string $name = null;

    protected SchemaBuilder $builder;

    public function __construct(SchemaBuilder $builder, array|string $columns)
    {
        $this->builder = $builder;
        $this->columns = is_array($columns) ? $columns : [$columns];
    }

    public function unique(): self
    {
        $this->type = 'UNIQUE';

        return $this;
    }

    public function fulltext(): self
    {
        $this->type = 'FULLTEXT';

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function add(): void
    {
        $name = $this->name ?? $this->generateName();
        $columns = implode('`, `', $this->columns);

        $sql = $this->type . ' INDEX `' . $name . '` (`' . $columns . '`)';

        $this->builder->addIndex($sql);
    }

    protected function generateName(): string
    {
        $type = strtolower($this->type);
        $columns = implode('_', $this->columns);

        return $type . '_' . $columns;
    }
}