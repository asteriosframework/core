<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Orm;

use Asterios\Core\Exception\ModelException;
use Asterios\Core\Exception\ModelInvalidArgumentException;

interface OrmQueryBuilderInterface
{
    /**
     * @param array|string|null $columns
     * @return self
     */
    public function select(array|string $columns = null): self;

    /**
     * @param bool $value
     * @return self
     */
    public function distinct(bool $value = true): self;

    /**
     * @param string|null $tableName
     * @param string|null $alias
     * @return self
     * @throws ModelException
     */
    public function from(?string $tableName = null, ?string $alias = null): self;

    /**
     * @param string $table
     * @param string $direction
     * @param string|null $alias
     * @return self
     */
    public function join(string $table, string $direction = 'LEFT', ?string $alias = null): self;

    /**
     * @param string $column1
     * @param string $column2
     * @return OrmQueryBuilderInterface
     * @throws ModelInvalidArgumentException
     */
    public function on(string $column1, string $column2): self;

    /**
     * @param string $column1
     * @param string $column2
     * @return OrmQueryBuilderInterface
     * @throws ModelInvalidArgumentException
     */
    public function orOn(string $column1, string $column2): self;

    /**
     * @param string $column
     * @param string|int|float|null|bool $operator
     * @param string|int|float|null|bool $value
     * @param bool $backticks
     * @param bool $formatValue
     * @return self
     */
    public function where(
        string $column,
        string|int|float|null|bool $operator = null,
        string|int|float|null|bool $value = null,
        bool $backticks = true,
        bool $formatValue = true
    ): self;

    /**
     * @param string $whereCondition
     * @param string $column
     * @param string|int|float|null|bool $operator
     * @param string|int|float|null|bool $value
     * @param bool $backticks
     * @return self
     */
    public function whereOpenByCondition(
        string $whereCondition,
        string $column,
        string|int|float|null|bool $operator,
        string|int|float|null|bool $value = null,
        bool $backticks = true
    ): self;

    /**
     * @param string $column
     * @param string|int|float|null|bool $operator
     * @param string|int|float|null|bool $value
     * @param bool $backticks
     * @return self
     */
    public function orWhere(
        string $column,
        string|int|float|null|bool $operator = null,
        string|int|float|null|bool $value = null,
        bool $backticks = true
    ): self;

    /**
     * @return self
     */
    public function whereOpen(): self;

    /**
     * @return self
     */
    public function whereClose(): self;

    /**
     * @param string $column
     * @param string|int|float|null|bool $operator
     * @param string|int|float|null|bool $value
     * @param bool $backticks
     * @return self
     */
    public function andWhereOpen(string $column, string|int|float|null|bool $operator, string|int|float|null|bool $value = null, bool $backticks = true): self;

    /**
     * @return self
     */
    public function andWhereClose(): self;

    /**
     * @param string $column
     * @param string|int|float|null|bool $operator
     * @param string|int|float|null|bool $value
     * @param bool $backticks
     * @return self
     */
    public function orWhereOpen(
        string $column,
        string|int|float|null|bool $operator = null,
        string|int|float|null|bool $value = null,
        bool $backticks = true
    ): self;

    /**
     * @return self
     */
    public function orWhereClose(): self;

    /**
     * @param array $groupBy
     * @return self
     */
    public function groupBy(array $groupBy): self;

    /**
     * @param string $column
     * @param string $direction
     * @param bool $backticks
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC', bool $backticks = true): self;

    /**
     * @param int $limit
     * @param int $offset
     * @return self
     * @throws ModelInvalidArgumentException
     */
    public function limit(int $limit, int $offset = 0): self;

    /**
     * @param string|array $columns
     * @param string $search
     * @param bool $booleanMode
     * @param bool $withWildcards
     * @return self
     */
    public function fulltext(string|array $columns, string $search, bool $booleanMode = true, bool $withWildcards = false): self;

    /**
     * @param string|array $columns
     * @param string $search
     * @param bool $booleanMode
     * @param bool $withWildcards
     * @return self
     */
    public function fulltextWithScore(string|array $columns, string $search, bool $booleanMode = true, bool $withWildcards = false): self;

    /**
     * @return string|null
     * @throws ModelException
     */
    public function compile(): ?string;

    /**
     * @return string|null
     * @throws ModelException
     */
    public function getCountCompile(): ?string;

    /**
     * @param ?string $query
     * @return self
     */
    public function query(string $query = null): self;

    /**
     * @return self
     */
    public function reset(): self;

    /**
     * @return self
     */
    public function open(): self;

    /**
     * @return self
     */
    public function close(): self;

    /**
     * @return self
     */
    public function and(): self;

    /**
     * @return self
     */
    public function or(): self;

    /**
     * @param array $options
     * @return self
     */
    public function applyWhereOptions(array $options): self;
}