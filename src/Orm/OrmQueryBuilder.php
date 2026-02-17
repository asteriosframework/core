<?php declare(strict_types=1);

namespace Asterios\Core\Orm;

use Asterios\Core\Contracts\Orm\OrmQueryBuilderInterface;
use Asterios\Core\Contracts\Orm\OrmSqlFormatterInterface;
use Asterios\Core\Enum\Orm\OperatorEnum;
use Asterios\Core\Exception\ModelInvalidArgumentException;

final class OrmQueryBuilder implements OrmQueryBuilderInterface
{
    private array $select = [];
    private bool $distinct = false;

    private ?array $from = null;

    private array $joins = [];
    private array $whereParts = [];
    private array $groupBy = [];
    private array $orderBy = [];
    private ?array $limit = null;

    private ?string $rawQuery = null;
    private string $nextBoolean = 'AND';

    public function __construct(
        private readonly OrmMetadata $metadata,
        private readonly OrmSqlFormatterInterface $formatter
    )
    {
    }

    /** @inheritDoc */
    public function select(array|string $columns = null): self
    {
        $new = $this->cloneInstance();

        if ($columns === null)
        {
            $new->select = ['*'];
        }
        else
        {
            $new->select = is_array($columns) ? $columns : [$columns];
        }

        return $new;
    }

    private function cloneInstance(): self
    {
        return clone $this;
    }

    /** @inheritDoc */
    public function distinct(bool $value = true): self
    {
        $new = $this->cloneInstance();
        $new->distinct = $value;

        return $new;
    }

    /** @inheritDoc */
    public function from(?string $tableName = null, ?string $alias = null): self
    {
        $new = $this->cloneInstance();

        $tableName ??= $this->metadata->table;
        $alias ??= $this->metadata->alias;

        $new->from = [
            'table' => $tableName,
            'alias' => $alias,
        ];

        return $new;
    }

    /** @inheritDoc */
    public function join(string $table, string $direction = 'LEFT', ?string $alias = null): self
    {
        $new = $this->cloneInstance();

        $new->joins[] = [
            'type' => strtoupper($direction),
            'table' => $table,
            'alias' => $alias,
            'conditions' => [],
        ];

        return $new;
    }

    /** @inheritDoc */
    public function on(string $column1, string $column2): self
    {
        return $this->addJoinCondition($column1, $column2, 'AND');
    }

    /**
     * @param string $col1
     * @param string $col2
     * @param string $boolean
     * @return self
     * @throws ModelInvalidArgumentException
     */
    private function addJoinCondition(string $col1, string $col2, string $boolean): self
    {
        if (!str_contains($col1, '.') || !str_contains($col2, '.'))
        {
            throw new ModelInvalidArgumentException(
                'Join columns must contain table.column format.'
            );
        }

        $new = $this->cloneInstance();
        $lastJoinIndex = array_key_last($new->joins);

        if ($lastJoinIndex === null)
        {
            throw new ModelInvalidArgumentException('No join defined for ON condition.');
        }

        $new->joins[$lastJoinIndex]['conditions'][] = [
            'boolean' => $boolean,
            'left' => $col1,
            'right' => $col2,
        ];

        return $new;
    }

    /** @inheritDoc */
    public function orOn(string $column1, string $column2): self
    {
        return $this->addJoinCondition($column1, $column2, 'OR');
    }

    /** @inheritDoc */
    public function orWhere(string $column, string|int|float|null|bool $operator = null, string|int|float|null|bool $value = null, bool $backticks = true): self
    {
        return $this->or()
            ->where($column, $operator, $value, $backticks);
    }

    /** @inheritDoc */
    public function where(
        string $column,
        string|int|float|null|bool $operator = null,
        string|int|float|null|bool $value = null,
        bool $backticks = true,
        bool $formatValue = true
    ): self
    {
        $new = $this->cloneInstance();

        if (!OperatorEnum::isOperator($operator))
        {
            $value = $operator;
            $operator = '=';
        }

        $new->whereParts[] = [
            'type' => 'condition',
            'boolean' => $new->nextBoolean,
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'backticks' => $backticks,
            'formatValue' => $formatValue,
        ];

        $new->nextBoolean = 'AND';

        return $new;
    }

    /** @inheritDoc */
    public function or(): self
    {
        $new = $this->cloneInstance();
        $new->nextBoolean = 'OR';

        return $new;
    }

    /** @inheritDoc */
    public function andWhereOpen(string $column, string|int|float|null|bool $operator, string|int|float|null|bool $value = null, bool $backticks = true): self
    {
        return $this->whereOpenByCondition('AND', $column, $operator, $value, $backticks);
    }

    /** @inheritDoc */
    public function whereOpenByCondition(
        string $whereCondition,
        string $column,
        string|int|float|null|bool $operator,
        string|int|float|null|bool $value = null,
        bool $backticks = true
    ): self
    {
        return $this->addBracket('(')
            ->where($column, $operator, $value, $backticks);
    }

    private function addBracket(string $bracket): self
    {
        $new = $this->cloneInstance();

        $new->whereParts[] = [
            'type' => 'bracket',
            'value' => $bracket,
            'boolean' => $new->nextBoolean,
        ];

        $new->nextBoolean = 'AND';

        return $new;
    }

    /** @inheritDoc */
    public function andWhereClose(): self
    {
        return $this->whereClose();
    }

    /** @inheritDoc */
    public function whereClose(): self
    {
        return $this->addBracket(')');
    }

    /** @inheritDoc */
    public function orWhereOpen(
        string $column,
        string|int|float|null|bool $operator = null,
        string|int|float|null|bool $value = null,
        bool $backticks = true
    ): self
    {
        return $this->or()
            ->whereOpenByCondition('OR', $column, $operator, $value, $backticks);
    }

    /** @inheritDoc */
    public function orWhereClose(): self
    {
        return $this->whereClose();
    }

    /** @inheritDoc */
    public function groupBy(array $groupBy): self
    {
        $new = $this->cloneInstance();
        $new->groupBy = $groupBy;

        return $new;
    }

    /** @inheritDoc */
    public function orderBy(string $column, string $direction = 'ASC', bool $backticks = true): self
    {
        $new = $this->cloneInstance();

        $new->orderBy[] = [
            'column' => $column,
            'direction' => strtoupper($direction),
            'backticks' => $backticks,
        ];

        return $new;
    }

    /** @inheritDoc */
    public function limit(int $limit, int $offset = 0): self
    {
        if ($limit <= 0)
        {
            throw new ModelInvalidArgumentException('Limit must be greater than 0.');
        }

        $new = $this->cloneInstance();
        $new->limit = ['limit' => $limit, 'offset' => $offset];

        return $new;
    }

    /** @inheritDoc */
    public function fulltextWithScore(string|array $columns, string $search, bool $booleanMode = true, bool $withWildcards = false): self
    {
        $new = $this->fulltext($columns, $search, $booleanMode, $withWildcards);

        $cols = is_array($columns)
            ? implode(',', array_map([$this->formatter, 'backticks'], $columns))
            : $this->formatter->backticks($columns);

        $mode = $booleanMode ? ' IN BOOLEAN MODE' : '';

        $expr = "MATCH($cols) AGAINST (" .
            $this->formatter->formatValue($search) . $mode . ") AS relevance";

        $new->select[] = $expr;

        return $new;
    }

    /** @inheritDoc */
    public function fulltext(string|array $columns, string $search, bool $booleanMode = true, bool $withWildcards = false): self
    {
        $new = $this->cloneInstance();

        $cols = is_array($columns)
            ? implode(',', array_map([$this->formatter, 'backticks'], $columns))
            : $this->formatter->backticks($columns);

        $mode = $booleanMode ? ' IN BOOLEAN MODE' : '';

        $expr = "MATCH($cols) AGAINST (" .
            $this->formatter->formatValue($search) . $mode . ")";

        $new->whereParts[] = [
            'type' => 'raw',
            'boolean' => $new->nextBoolean,
            'expression' => $expr,
        ];

        $new->nextBoolean = 'AND';

        return $new;
    }

    /** @inheritDoc */
    public function compile(): string
    {
        if ($this->rawQuery !== null)
        {
            return $this->rawQuery;
        }

        $sql = 'SELECT ';

        if ($this->distinct)
        {
            $sql .= 'DISTINCT ';
        }

        $sql .= $this->compileSelect();

        $sql .= $this->compileFrom();
        $sql .= $this->compileJoins();
        $sql .= $this->compileWhere();
        $sql .= $this->compileGroupBy();
        $sql .= $this->compileOrderBy();
        $sql .= $this->compileLimit();

        return trim($sql);
    }

    /**
     * @return string
     */
    private function compileSelect(): string
    {
        if (empty($this->select))
        {
            return '*';
        }

        $columns = [];

        foreach ($this->select as $column)
        {

            if ($column === '*')
            {
                $columns[] = '*';
                continue;
            }

            if (str_ends_with($column, '.*'))
            {
                $columns[] = $column;
                continue;
            }

            if (str_contains($column, '(') || str_contains($column, ')') || str_contains($column, ' ') || stripos($column, ' as ') !== false
            )
            {
                $columns[] = $column;
                continue;
            }

            $columns[] = $this->formatter->backticks($column);
        }

        return implode(', ', $columns);
    }

    private function compileFrom(): string
    {
        $from = $this->from ?? [
            'table' => $this->metadata->table,
            'alias' => $this->metadata->alias,
        ];

        $sql = ' FROM ' . $this->formatter->backticks($from['table']);

        if ($from['alias'])
        {
            $sql .= ' ' . $from['alias'];
        }

        return $sql;
    }

    private function compileJoins(): string
    {
        if (empty($this->joins))
        {
            return '';
        }

        $sql = '';

        foreach ($this->joins as $join)
        {
            $sql .= ' ' . $join['type'] . ' JOIN ';
            $sql .= $this->formatter->backticks($join['table']);

            if ($join['alias'])
            {
                $sql .= ' ' . $join['alias'];
            }

            if (!empty($join['conditions']))
            {
                $first = true;
                foreach ($join['conditions'] as $cond)
                {
                    $prefix = $first ? ' ON ' : ' ' . $cond['boolean'] . ' ';
                    $sql .= $prefix
                        . $this->formatter->backticks($cond['left'])
                        . ' = '
                        . $this->formatter->backticks($cond['right']);
                    $first = false;
                }
            }
        }

        return $sql;
    }

    /**
     * @return string
     */
    private function compileWhere(): string
    {
        if (empty($this->whereParts))
        {
            return '';
        }

        $sql = ' WHERE ';
        $first = true;
        $previousWasOpenBracket = false;

        foreach ($this->whereParts as $part)
        {

            if ($part['type'] === 'bracket')
            {

                if ($part['value'] === '(')
                {

                    if (!$first)
                    {
                        $sql .= ' ' . $part['boolean'] . ' ';
                    }

                    $sql .= '(';
                    $previousWasOpenBracket = true;
                    $first = false;
                    continue;
                }

                if ($part['value'] === ')')
                {
                    $sql .= ')';
                    $previousWasOpenBracket = false;
                    continue;
                }
            }

            if (!$first && !$previousWasOpenBracket)
            {
                $sql .= ' ' . ($part['boolean'] ?? 'AND') . ' ';
            }

            if ($part['type'] === 'condition')
            {

                $column = $part['backticks']
                    ? $this->formatter->backticks($part['column'])
                    : $part['column'];

                $sql .= $column . ' ' . $part['operator'];

                if (!$this->formatter->isOperatorNull($part['operator']))
                {

                    $value = $part['formatValue']
                        ? $this->formatter->formatValue($part['value'])
                        : $part['value'];

                    $sql .= ' ' . $value;
                }

            }
            elseif ($part['type'] === 'raw')
            {

                $sql .= $part['expression'];
            }

            $previousWasOpenBracket = false;
            $first = false;
        }

        return $sql;
    }

    private function compileGroupBy(): string
    {
        if (empty($this->groupBy))
        {
            return '';
        }

        return ' GROUP BY ' . implode(', ',
                array_map(
                    [$this->formatter, 'backticks'],
                    $this->groupBy
                ));
    }

    private function compileOrderBy(): string
    {
        if (empty($this->orderBy))
        {
            return '';
        }

        $parts = [];

        foreach ($this->orderBy as $order)
        {
            $col = $order['backticks']
                ? $this->formatter->backticks($order['column'])
                : $order['column'];

            $parts[] = $col . ' ' . $order['direction'];
        }

        return ' ORDER BY ' . implode(', ', $parts);
    }

    private function compileLimit(): string
    {
        if (!$this->limit)
        {
            return '';
        }

        return ' LIMIT ' . $this->limit['offset'] . ', ' . $this->limit['limit'];
    }

    /** @inheritDoc */
    public function getCountCompile(): string
    {
        $sql = 'SELECT COUNT(*) AS count';
        $sql .= $this->compileFrom();
        $sql .= $this->compileJoins();
        $sql .= $this->compileWhere();
        $sql .= $this->compileGroupBy();

        return trim($sql);
    }

    /** @inheritDoc */
    public function query(string $query = null): self
    {
        $new = $this->cloneInstance();
        $new->rawQuery = $query;

        return $new;
    }

    /** @inheritDoc */
    public function reset(): self
    {
        return new self($this->metadata, $this->formatter);
    }

    /** @inheritDoc */
    public function open(): self
    {
        return $this->whereOpen();
    }

    /** @inheritDoc */
    public function whereOpen(): self
    {
        return $this->addBracket('(');
    }

    /** @inheritDoc */
    public function close(): self
    {
        return $this->whereClose();
    }

    /** @inheritDoc */
    public function and(): self
    {
        $new = $this->cloneInstance();
        $new->nextBoolean = 'AND';

        return $new;
    }

    /** @inheritDoc */
    public function applyWhereOptions(array $options): self
    {
        $builder = $this;

        if (isset($options['where']))
        {
            foreach ($options['where'] as $column => $value)
            {
                if (is_array($value))
                {
                    $builder = $builder->where($value[0], $value[1] ?? '=', $value[2] ?? null);
                }
                else
                {
                    $builder = $builder->where($column, '=', $value);
                }
            }
        }

        return $builder;
    }
}
