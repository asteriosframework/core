<?php declare(strict_types=1);

namespace Asterios\Core\Orm;

use Asterios\Core\Contracts\Orm\OrmQueryBuilderInterface;
use Asterios\Core\Contracts\Orm\OrmSqlFormatterInterface;
use Asterios\Core\Exception\ModelInvalidArgumentException;

class OrmQueryBuilder implements OrmQueryBuilderInterface
{
    protected array $whereStatement = [];
    protected ?string $groupByStatement = null;
    protected ?string $orderByStatement = null;
    protected ?string $limitStatement = null;
    protected bool $selectDistinct = false;
    protected string $selectStatement = '';
    protected ?string $fromStatement = null;
    protected ?string $joinStatement = null;
    protected ?string $queryStatement = null;
    protected string $nextWhereBoolean = 'AND';
    protected OrmMetadata $metadata;
    protected OrmSqlFormatterInterface $formatter;

    public function __construct(
        OrmMetadata $metadata,
        OrmSqlFormatterInterface $formatter
    )
    {
        $this->metadata = $metadata;
        $this->formatter = $formatter;
    }

    /**
     * @inheritDoc
     */
    public function select(array|string $columns = null): OrmQueryBuilderInterface
    {
        if (empty($columns))
        {
            $this->selectStatement .= '*';
        }
        else
        {
            $count = 1;

            if (is_array($columns))
            {
                $total_columns = count($columns);

                foreach ($columns as $value)
                {
                    if ($total_columns === 1)
                    {
                        $this->selectStatement .= $this->formatter->backticks($value);
                    }
                    elseif ($count < $total_columns)
                    {
                        $this->selectStatement .= $this->formatter->backticks($value) . ', ';
                    }
                    else
                    {
                        $this->selectStatement .= $this->formatter->backticks($value);
                    }
                    $count++;
                }
            }
            else
            {
                $this->selectStatement = $columns;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function distinct(bool $value = true): OrmQueryBuilderInterface
    {
        $this->selectDistinct = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function join(string $table, string $direction = 'LEFT', ?string $alias = null): OrmQueryBuilderInterface
    {
        if (!empty($table))
        {

            $this->joinStatement .= $direction . ' JOIN ' . $this->formatter->backticks($table);

            if (null !== $alias)
            {
                $this->joinStatement .= ' ' . $alias . ' ';
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function on(string $column1, string $column2): OrmQueryBuilderInterface
    {
        if (!empty($column1) && !empty($column2))
        {
            if (!str_contains($column1, '.'))
            {
                throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Column1 value "' . $column1 . '" must have table name and column name separated with a dot! Example: "table_name.row"');
            }

            if (!str_contains($column2, '.'))
            {
                throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Column2 value "' . $column2 . '" must have table name and column name separated with a dot! Example: "table_name.row"');
            }

            $this->joinStatement .= 'ON ' . $this->formatter->backticks($column1) . ' = ' . $this->formatter->backticks($column2) . ' ';

            return $this;
        }

        throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Column1 and Column2 must have table name and column name separated with a dot! Example: "table_name.row"');

    }

    /**
     * @inheritDoc
     */
    public function orOn(string $column1, string $column2): OrmQueryBuilderInterface
    {
        if (!empty($column1) && !empty($column2))
        {
            if (!str_contains($column1, '.'))
            {
                throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Column1 value "' . $column1 . '" must have table name and column name separated with a dot! Example: "table_name.row"');
            }

            if (!str_contains($column2, '.'))
            {
                throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Column2 value "' . $column2 . '" must have table name and column name separated with a dot! Example: "table_name.row"');
            }

            $this->joinStatement .= ' OR ' . $this->formatter->backticks($column1) . ' = ' . $this->formatter->backticks($column2) . ' ';

            return $this;
        }

        throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Column1 and Column2 must have table name and column name separated with a dot! Example: "table_name.row"');

    }

    /**
     * @inheritDoc
     */
    public function where(
        string $column,
        string|int|null $operator = null,
        string|int|float|null $value = null,
        bool $backticks = true,
        bool $formatValue = true
    ): OrmQueryBuilderInterface
    {
        if (func_num_args() === 2)
        {
            $value = $operator;
            $operator = '=';
        }

        if ($operator === 'IN')
        {
            $condition = $this->formatter->formatInOperator($value);
        }
        elseif ($formatValue)
        {
            $condition = $this->formatter->formatValue($value);
        }
        else
        {
            $condition = $value;
        }

        $_column = $column;

        if ($backticks)
        {
            $_column = $this->formatter->backticks($column);
        }

        $where = $_column . ' ' . $operator;

        if (!$this->formatter->isOperatorNull($operator))
        {
            $where .= ' ' . $condition;
        }

        $this->appendWhere($where);

        return $this;
    }

    /**
     * @param string $condition
     * @return void
     */
    protected function appendWhere(string $condition): void
    {
        if (empty($this->whereStatement))
        {
            $this->whereStatement[] = 'WHERE ' . $condition;
        }
        else
        {
            $this->whereStatement[] = ' ' . $this->nextWhereBoolean . ' ' . $condition;
        }

        $this->nextWhereBoolean = 'AND';
    }

    /**
     * @inheritDoc
     */
    public function orWhere(
        string $column,
        string|int|null $operator = null,
        string|int|float|null $value = null,
        bool $backticks = true
    ): OrmQueryBuilderInterface
    {
        if (func_num_args() === 2)
        {
            $value = $operator;
            $operator = '=';
        }

        $where = $this->getWhere($operator, $value, $column, $backticks);

        $this->whereStatement[] = ' OR ' . $where;

        return $this;
    }

    /**
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param string $column
     * @param bool $backticks
     * @return string
     */
    protected function getWhere(string|int|null $operator, string|int|float|null $value, string $column, bool $backticks): string
    {
        if ($operator === 'IN')
        {
            $condition = $this->formatter->formatInOperator($value);
        }
        else
        {
            $condition = $this->formatter->formatValue($value);
        }

        $_column = $column;

        if ($backticks)
        {
            $_column = $this->formatter->backticks($column);
        }

        $where = $_column . ' ' . $operator;

        if (!$this->formatter->isOperatorNull($operator))
        {
            $where .= ' ' . $condition;
        }

        return $where;
    }

    /**
     * @inheritDoc
     */
    public function whereOpen(): OrmQueryBuilderInterface
    {
        $this->whereStatement[] = ' AND ' . $this->formatter->open();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function andWhereOpen(
        string $column,
        string|int|null $operator,
        string|int|float|null $value = null,
        bool $backticks = true
    ): OrmQueryBuilderInterface
    {
        if (func_num_args() === 2)
        {
            return $this->whereOpenByCondition('AND', $column, null, $operator, $backticks);
        }

        return $this->whereOpenByCondition('AND', $column, $operator, $value, $backticks);
    }

    /**
     * @inheritDoc
     */
    public function whereOpenByCondition(
        string $whereCondition,
        string $column,
        string|int|null $operator,
        string|int|float|null $value = null,
        bool $backticks = true
    ):
    OrmQueryBuilderInterface
    {
        if ($operator === null)
        {
            $operator = '=';
        }

        $where = $this->getWhere($operator, $value, $column, $backticks);

        $this->whereStatement[] = ' ' . $whereCondition . ' ' . $this->formatter->open() . $where;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function andWhereClose(): OrmQueryBuilderInterface
    {
        $this->whereClose();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereClose(): OrmQueryBuilderInterface
    {
        $this->whereStatement[] = $this->formatter->close();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orWhereOpen(string $column, string|int|null $operator = null, string|int|float|null $value = null, bool $backticks = true):
    OrmQueryBuilderInterface
    {
        if (func_num_args() === 2)
        {
            return $this->whereOpenByCondition('OR', $column, null, $operator, $backticks);
        }

        return $this->whereOpenByCondition('OR', $column, $operator, $value, $backticks);
    }

    /**
     * @inheritDoc
     */
    public function orWhereClose(): OrmQueryBuilderInterface
    {
        $this->whereClose();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function groupBy(array $groupBy): OrmQueryBuilderInterface
    {
        if (!empty($groupBy))
        {
            if (null === $this->groupByStatement)
            {
                $this->groupByStatement = ' GROUP BY ';
            }

            $totalGroupBy = count($groupBy);

            $count = 1;

            foreach ($groupBy as $value)
            {
                if ($totalGroupBy === 1)
                {
                    $this->groupByStatement .= $this->formatter->backticks($value);
                }
                elseif ($count < $totalGroupBy)
                {
                    $this->groupByStatement .= $this->formatter->backticks($value) . ', ';
                }
                else
                {
                    $this->groupByStatement .= $this->formatter->backticks($value);
                }
                $count++;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orderBy(string $column, string $direction = 'ASC', bool $backticks = true): OrmQueryBuilderInterface
    {
        $separator = ',';
        $orderBySql = '';

        if (null === $this->orderByStatement)
        {
            $separator = '';
            $orderBySql = 'ORDER BY';
        }

        $_column = $column;

        if ($backticks)
        {
            $_column = $this->formatter->backticks($column);
        }

        $this->orderByStatement .= $separator . ' ' . $orderBySql . ' ' . $_column . ' ' . $direction;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function limit(int $limit, int $offset = 0): OrmQueryBuilderInterface
    {
        if ($limit === 0 && $offset === 0)
        {
            throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Required parameter not given!');
        }

        $this->limitStatement = ' LIMIT ' . $offset . ', ' . $limit;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fulltext(array|string $columns, string $search, bool $booleanMode = true, bool $withWildcards = false): OrmQueryBuilderInterface
    {
        [$cols, $search, $mode] = $this->fulltextHelper($columns, $booleanMode, $withWildcards, $search);
        $expr = 'MATCH(' . $cols . ') AGAINST (' . $this->formatter->formatValue($search) . $mode . ')';

        $this->appendWhere($expr);

        return $this;
    }

    /**
     * @param array|string $columns
     * @param bool $booleanMode
     * @param bool $withWildcards
     * @param string $search
     * @return array
     */
    protected function fulltextHelper(array|string $columns, bool $booleanMode, bool $withWildcards, string $search): array
    {
        $cols = is_array($columns) ? implode(',', array_map([$this, 'backticks'], $columns)) : $this->formatter->backticks($columns);

        if ($booleanMode && $withWildcards)
        {
            $search = implode(' ',
                array_map(static fn($w) => '+' . $w . '*',
                    preg_split('/\s+/', trim($search))
                ));
        }

        $mode = $booleanMode ? ' IN BOOLEAN MODE' : '';

        return [$cols, $search, $mode];
    }

    /**
     * @inheritDoc
     */
    public function fulltextWithScore(array|string $columns, string $search, bool $booleanMode = true, bool $withWildcards = false): OrmQueryBuilderInterface
    {
        [$cols, $search, $mode] = $this->fulltextHelper($columns, $booleanMode, $withWildcards, $search);
        $expr = 'MATCH(' . $cols . ') AGAINST (' . $this->formatter->formatValue($search) . $mode . ')';

        if (empty($this->selectStatement))
        {
            $this->selectStatement = '*';
        }

        $this->selectStatement .= ', ' . $expr . ' AS relevance';

        $this->appendWhere($expr);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function compile(): ?string
    {
        if (empty($this->selectStatement))
        {
            $this->selectStatement = '*';
        }

        if (null === $this->queryStatement)
        {
            $this->queryStatement = 'SELECT ';

            if ($this->selectDistinct)
            {
                $this->queryStatement .= 'DISTINCT ';
            }

            if (null === $this->fromStatement)
            {
                $this->from();
            }

            if ($this->getTableAlias() !== null)
            {
                $this->fromStatement .= ' ' . $this->getTableAlias();
            }

            $this->queryStatement .= $this->selectStatement . $this->fromStatement . ' ';

            if (null !== $this->joinStatement)
            {
                $this->queryStatement .= $this->joinStatement . ' ';
            }

            if (!empty($this->whereStatement))
            {
                foreach ($this->whereStatement as $value)
                {
                    $this->queryStatement .= $value;
                }
            }

            if (null !== $this->groupByStatement)
            {
                $this->queryStatement .= $this->groupByStatement;
            }

            if (null !== $this->orderByStatement)
            {
                $this->queryStatement .= $this->orderByStatement;
            }

            if (null !== $this->limitStatement)
            {
                $this->queryStatement .= $this->limitStatement;
            }
        }

        return $this->queryStatement;
    }

    /**
     * @inheritDoc
     */
    public function from(?string $tableName = null, ?string $alias = null): OrmQueryBuilderInterface
    {
        if (null === $tableName)
        {
            $this->fromStatement = ' FROM ' . $this->formatter->backticks($this->metadata->table);
        }
        else
        {
            $this->fromStatement = ' FROM ' . $this->formatter->backticks($tableName);

            if (null !== $alias)
            {
                $this->fromStatement .= ' ' . $alias;
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    protected function getTableAlias(): ?string
    {
        return $this->metadata->alias;
    }

    /**
     * @inheritDoc
     */
    public function getCountCompile(): ?string
    {
        $countQueryStatement = 'SELECT ';

        $this->from();

        if ($this->getTableAlias() !== null)
        {
            $this->fromStatement .= ' ' . $this->getTableAlias();
        }

        $countQueryStatement .= 'COUNT(*) AS count' . $this->fromStatement . ' ';

        if (null !== $this->joinStatement)
        {
            $countQueryStatement .= $this->joinStatement . ' ';
        }

        if (!empty($this->whereStatement))
        {
            foreach ($this->whereStatement as $value)
            {
                $countQueryStatement .= $value;
            }
        }

        if (null !== $this->groupByStatement)
        {
            $countQueryStatement .= $this->groupByStatement;
        }

        return $countQueryStatement;
    }

    /**
     * @inheritDoc
     */
    public function and(): self
    {
        $this->nextWhereBoolean = 'AND';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function or(): self
    {
        $this->nextWhereBoolean = 'OR';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function query($query = null): OrmQueryBuilderInterface
    {
        if (null !== $query)
        {
            $this->queryStatement = $query;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function reset(): self
    {
        $this->whereStatement = [];
        $this->groupByStatement = null;
        $this->orderByStatement = null;
        $this->limitStatement = null;
        $this->selectDistinct = false;
        $this->selectStatement = '';
        $this->fromStatement = null;
        $this->joinStatement = null;
        $this->queryStatement = null;
        $this->nextWhereBoolean = 'AND';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function open(): OrmQueryBuilderInterface
    {
        $this->whereStatement[] = '(';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function close(): OrmQueryBuilderInterface
    {
        $this->whereStatement[] = ')';

        return $this;
    }
}