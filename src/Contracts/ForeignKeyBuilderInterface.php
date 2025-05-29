<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

interface ForeignKeyBuilderInterface
{

    /**
     * @param string $table
     * @param string $column
     * @return self
     */
    public function references(string $table, string $column = 'id'): self;

    /**
     * @param string $action
     * @return self
     */
    public function onDelete(string $action): self;

    /**
     * @param string $action
     * @return self
     */
    public function onUpdate(string $action): self;

    /**
     * @return void
     */
    public function add(): void;
}
