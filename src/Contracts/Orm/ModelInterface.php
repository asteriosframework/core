<?php

declare(strict_types=1);

namespace Asterios\Core\Contracts\Orm;

use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\ModelException;
use Asterios\Core\Exception\ModelInvalidArgumentException;
use Asterios\Core\Exception\ModelPrimaryKeyException;
use Asterios\Core\Exception\ModelPropertyException;

interface ModelInterface
{
    /**
     * @var string
     */
    public const string EXECUTE_MODE_READ = 'read';
    /**
     * @var string
     */
    public const string EXECUTE_MODE_WRITE = 'write';

    /**
     * @param int|string $id
     * @param array $options
     * @return static
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     * @throws ConfigLoadException
     */
    public static function find(int|string $id = 'all', array $options = []): static;

    /**
     * @return static
     */
    public static function forge(): static;

    /**
     * @return static
     * @throws ModelException
     */
    public function reset(): static;

    /**
     * @return string
     * @throws ModelException
     */
    public function table(): string;

    /**
     * @param array $options
     * @return static
     * @throws ModelException
     * @throws ConfigLoadException
     */
    public function findAll(array $options = []): static;

    /**
     * @param array $options
     * @return static
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     * @throws ConfigLoadException
     */
    public function findFirst(array $options = []): static;

    /**
     * @return static
     */
    public function prepareFindResult(): static;

    /**
     * @param array $options
     * @return static
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     * @throws ConfigLoadException
     */
    public function findLast(array $options = []): static;

    /**
     * @param string|int $id
     * @return static
     * @throws ModelException
     * @throws ConfigLoadException
     */
    public function findByPrimaryKey(string|int $id): static;

    /**
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @param bool $formatValue
     * @return static
     */
    public function where(
        string $column,
        string|int|null $operator = null,
        string|int|float|null $value = null,
        bool $backticks = true,
        bool $formatValue = true
    ): static;

    /**
     * @param string|null $tableName
     * @param string|null $alias
     * @return static
     * @throws ModelException
     */
    public function from(?string $tableName = null, ?string $alias = null): static;

    /**
     * @param null|array|string $columns
     * @return static
     */
    public function select(null|array|string $columns = null): static;

    /**
     * @param array $groupBy
     * @return static
     */
    public function groupBy(array $groupBy): static;

    /**
     * @param string $column
     * @param string $direction
     * @param bool $backticks
     * @return static
     */
    public function orderBy(string $column, string $direction = 'ASC', bool $backticks = true): static;

    /**
     * @param int $limit
     * @param int $offset
     * @return static
     * @throws ModelInvalidArgumentException
     */
    public function limit(int $limit, int $offset = 0): static;

    /**
     * @param string $option
     * @return static
     * @throws ModelException
     * @throws ConfigLoadException
     */
    public function execute(string $option = self::EXECUTE_MODE_READ): static;

    /**
     * @return string|null
     * @throws ModelException
     */
    public function compile(): ?string;

    /**
     * @return array|false
     */
    public function getResult(): array|false;

    /**
     * @return string
     */
    public function primaryKey(): string;

    /**
     * @param string|array $columns
     * @param string $search
     * @param bool $booleanMode
     * @param bool $withWildcards
     * @return static
     */
    public function fulltext(
        string|array $columns,
        string $search,
        bool $booleanMode = true,
        bool $withWildcards = false
    ): static;

    /**
     * @param string|array $columns
     * @param string $search
     * @param bool $booleanMode
     * @param bool $withWildcards
     * @return static
     */
    public function fulltextWithScore(
        string|array $columns,
        string $search,
        bool $booleanMode = true,
        bool $withWildcards = false
    ): static;

    /**
     * @param bool $value
     * @return static
     */
    public function distinct(bool $value = true): static;

    /**
     * @param string $table
     * @param string $direction
     * @param string|null $alias
     * @return static
     */
    public function join(string $table, string $direction = 'LEFT', ?string $alias = null): static;

    /**
     * @param string $column1 ^
     * @param string $column2
     * @return static
     * @throws ModelInvalidArgumentException
     */
    public function on(string $column1, string $column2): static;

    /**
     * @param string $column1
     * @param string $column2
     * @return static
     * @throws ModelInvalidArgumentException
     */
    public function orOn(string $column1, string $column2): static;

    /**
     * @param string $column
     * @param string|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return static
     */
    public function orWhere(
        string $column,
        ?string $operator = null,
        string|int|float|null $value = null,
        bool $backticks = true
    ): static;

    /**
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return static
     */
    public function orWhereOpen(
        string $column,
        string|int|null $operator = null,
        string|int|float|null $value = null,
        bool $backticks = true
    ): static;

    /**
     * @param string $where_condition
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return static
     */
    public function whereOpenByCondition(
        string $where_condition,
        string $column,
        string|int|null $operator,
        string|int|float|null $value = null,
        bool $backticks = true
    ): static;

    /**
     * @return static
     */
    public function orWhereClose(): static;

    /**
     * @return static
     */
    public function whereClose(): static;

    /**
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return static
     */
    public function andWhereOpen(
        string $column,
        string|int|null $operator,
        string|int|float|null $value = null,
        bool $backticks = true
    ): static;

    /**
     * @return static
     */
    public function andWhereClose(): static;

    /**
     * @return static
     */
    public function whereOpen(): static;

    /**
     * @return static
     */
    public function and(): static;

    /**
     * @return static
     */
    public function or(): static;

    /**
     * @return static
     */
    public function open(): static;

    /**
     * @return static
     */
    public function close(): static;

    /**
     * @param string|null $query
     * @return static
     */
    public function query(?string $query = null): static;

    /**
     * @return array|false
     */
    public function asArray(): array|false;

    /**
     * @return object|false
     */
    public function asObject(): object|false;

    /**
     * @param string|array $property
     * @param string|int|float|null $value
     * @return static
     * @throws ModelException
     * @throws ModelPrimaryKeyException
     * @throws ModelPropertyException
     */
    public function set(string|array $property, string|int|float|null $value = null): static;

    /**
     * @param string $property
     * @return bool
     */
    public function propertyExists(string $property): bool;

    /**
     * @param bool $keysOnly
     * @return array
     */
    public function properties(bool $keysOnly = false): array;

    /**
     * @return bool|int|string
     * @throws ModelException
     * @throws ModelPropertyException
     * @throws ConfigLoadException
     */
    public function save(): bool|int|string;

    /**
     * @param int|string $id
     * @param array $data
     * @return bool
     * @throws ConfigLoadException
     * @throws ModelException
     * @throws ModelPropertyException
     */
    public function update(int|string $id, array $data = []): bool;

    /**
     * @param array $array
     * @return string|false
     * @throws ConfigLoadException
     */
    public function prepareUpdate(array $array): false|string;

    /**
     * @param string|int|float|null|bool $value
     * @return string
     * @throws ConfigLoadException
     */
    public function escape(string|int|float|null|bool $value): string;

    /**
     * @param array $data
     * @return false|int|string
     * @throws ModelException
     * @throws ModelPropertyException
     * @throws ConfigLoadException
     */
    public function insert(array $data = []): false|int|string;

    /**
     * @param array $array
     * @return bool|array
     * @throws ConfigLoadException
     */
    public function prepareInsert(array $array): bool|array;

    /**
     * @param string|int $id
     * @return bool
     * @throws ModelException
     * @throws ConfigLoadException
     */
    public function delete(string|int $id): bool;

    /**
     * @param string $property
     * @return false|array
     */
    public function dataValue(string $property): false|array;

    /**
     * @param string $property
     * @return false|array
     */
    public function dataType(string $property): false|array;

    /**
     * @return array
     * @throws ConfigLoadException
     * @throws ModelException
     */
    public function fieldNames(): array;

    /**
     * @return bool
     */
    public function hasResult(): bool;

    /**
     * @return static
     * @throws ConfigLoadException
     * @throws ModelException
     */
    public function getCount(): static;

    /**
     * @return string|null
     * @throws ModelException
     */
    public function getCountCompile(): ?string;

    /**
     * @return string|null
     */
    public function getTableAlias(): ?string;

    /**
     * @param string $alias
     * @return static
     * @throws ModelException
     */
    public function setTableAlias(string $alias): static;

    /**
     * @param string $columnName
     * @return string|int|float|null
     * @throws ModelException
     */
    public function getDefault(string $columnName): string|int|float|null;
}
