<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces;

use Asterios\Core\Db\Builder\ColumnDefinitionBuilder;
use Asterios\Core\Db\Builder\ForeignKeyBuilder;
use Asterios\Core\Db\Builder\IndexBuilder;

interface SchemaBuilderInterface
{
    /**
     * @param string $name
     * @return ColumnDefinitionBuilder
     */
    public function id(string $name = 'id'): ColumnDefinitionBuilder;

    /**
     * @param string $name
     * @param int $length
     * @return ColumnDefinitionBuilder
     */
    public function string(string $name, int $length = 255): ColumnDefinitionBuilder;

    /**
     * @param string $name
     * @param bool $unsigned
     * @return ColumnDefinitionBuilder
     */
    public function int(string $name, bool $unsigned = true): ColumnDefinitionBuilder;

    /**
     * @param string $name
     * @param bool $unsigned
     * @return ColumnDefinitionBuilder
     */
    public function smallInt(string $name, bool $unsigned = true): ColumnDefinitionBuilder;

    /**
     * @param string $name
     * @param bool $unsigned
     * @return ColumnDefinitionBuilder
     */
    public function bigInt(string $name, bool $unsigned = true): ColumnDefinitionBuilder;

    /**
     * @param string $name
     * @return ColumnDefinitionBuilder
     */
    public function boolean(string $name): ColumnDefinitionBuilder;

    /**
     * @param string $name
     * @param array $values
     * @return ColumnDefinitionBuilder
     */
    public function enum(string $name, array $values): ColumnDefinitionBuilder;

    /**
     * @param string $name
     * @return ColumnDefinitionBuilder
     */
    public function text(string $name): ColumnDefinitionBuilder;

    /**
     * @param string $name
     * @return ColumnDefinitionBuilder
     */
    public function mediumText(string $name): ColumnDefinitionBuilder;

    /**
     * @param string $name
     * @return ColumnDefinitionBuilder
     */
    public function json(string $name): ColumnDefinitionBuilder;

    /**
     * @param string $name
     * @param int $length
     * @return ColumnDefinitionBuilder
     */
    public function char(string $name, int $length = 1): ColumnDefinitionBuilder;

    /**
     * @param string $name
     * @return ColumnDefinitionBuilder
     */
    public function dateTime(string $name): ColumnDefinitionBuilder;

    /**
     * @param string $createdAt
     * @param string $updatedAt
     * @return self
     */
    public function timestamps(string $createdAt = 'created_at', string $updatedAt = 'updated_at'): self;

    /**
     * @param string $column
     * @return self
     */
    public function createdAt(string $column = 'created_at'): self;

    /**
     * @param string $column
     * @return self
     */
    public function updatedAt(string $column = 'updated_at'): self;

    /**
     * @param string $column
     * @return self
     */
    public function deletedAt(string $column = 'deleted_at'): self;

    /**
     * @param int $value
     * @return self
     */
    public function precision(int $value): self;

    /**
     * @param string $column
     * @return self
     */
    public function softDeletes(string $column = 'deleted_at'): self;

    /**
     * @param string $name
     * @param string $type
     * @return ColumnDefinitionBuilder
     */
    public function column(string $name, string $type): ColumnDefinitionBuilder;

    /**
     * @param string $sql
     * @return void
     */
    public function addColumn(string $sql): void;

    /**
     * @param string|array $columns
     * @return IndexBuilder
     */
    public function index(string|array $columns): IndexBuilder;

    /**
     * @param string $sql
     * @return void
     */
    public function addIndex(string $sql): void;

    /**
     * @param string $column
     * @return ForeignKeyBuilder
     */
    public function foreign(string $column): ForeignKeyBuilder;

    /**
     * @param string $sql
     * @return void
     */
    public function addForeignKey(string $sql): void;

    /**
     * @return array
     */
    public function build(): array;
}