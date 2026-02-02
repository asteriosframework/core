<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\ModelException;
use Asterios\Core\Exception\ModelInvalidArgumentException;
use Asterios\Core\Exception\ModelPrimaryKeyException;
use Asterios\Core\Exception\ModelPropertyException;

class Model
{
    public const string SQL_COMMAND_SELECT = 'SELECT';
    public const string SQL_COMMAND_FROM = 'FROM';
    public const string SQL_COMMAND_UPDATE = 'UPDATE';
    public const string SQL_COMMAND_DELETE = 'DELETE';
    public const string SQL_COMMAND_INSERT = 'INSERT INTO';
    public const string SQL_COMMAND_VALUES = 'VALUES';
    public const string SQL_COMMAND_SET = 'SET';

    public const string SQL_ORDER_BY_ASC = 'ASC';
    public const string SQL_ORDER_BY_DESC = 'DESC';

    public const string SQL_CLAUSE_JOIN = 'JOIN';
    public const string SQL_CLAUSE_ON = 'ON';
    public const string SQL_CLAUSE_INNER = 'INNER';
    public const string SQL_CLAUSE_LIMIT = 'LIMIT';

    public const string JOIN_LEFT = 'LEFT';
    public const string JOIN_RIGHT = 'RIGHT';
    public const string JOIN_INNER = 'INNER';

    public const string SQL_STATEMENT_WHERE = 'WHERE';
    public const string SQL_STATEMENT_AND = 'AND';
    public const string SQL_STATEMENT_OR = 'OR';
    public const string SQL_STATEMENT_EMPTY = '';
    public const string SQL_STATEMENT_GROUP_BY = 'GROUP BY';
    public const string SQL_STATEMENT_ORDER_BY = 'ORDER BY';

    public const string OPERATOR_EQUAL = '=';
    public const string OPERATOR_NOT_EQUAL = '!=';
    public const string OPERATOR_GT = '>';
    public const string OPERATOR_GT_OR_EQUAL = '>=';
    public const string OPERATOR_LT = '<';
    public const string OPERATOR_LT_OR_EQUAL = '<=';
    public const string OPERATOR_BETWEEN = 'BETWEEN';
    public const string OPERATOR_LIKE = 'LIKE';
    public const string OPERATOR_IN = 'IN';
    public const string OPERATOR_NOT_IN = 'NOT IN';
    public const string OPERATOR_IS_NULL = 'IS NULL';
    public const string OPERATOR_IS_NOT_NULL = 'IS NOT NULL';

    public const string EXECUTE_MODE_READ = 'read';
    public const string EXECUTE_MODE_WRITE = 'write';

    public const int DEFAULT_CACHE_LIFETIME = 60;

    protected string $connection = 'default';

    protected $class;
    protected $table_name = '';
    /** @var null|string $table_alias */
    protected $table_alias;
    protected $primary_key = '';
    /** @var array */
    protected $properties = [];
    /** @var array */
    protected $update = [];
    /** @var array */
    protected $where_statement = [];
    protected $group_by_statement;
    protected $order_by_statement;
    protected $limit_statement;
    /** @var false | mixed */
    protected $result = false;
    protected bool $select_distinct = false;
    protected $select_statement;
    protected $from_statement;
    protected $join_statement;
    protected $query_statement;
    protected $data = [];
    protected $_id;
    protected $db_schema = [];
    /** @var null|int */
    protected static $app_id = null;

    protected string $next_where_boolean = self::SQL_STATEMENT_AND;

    /**
     * This method can be used in three ways: find a specific id (primary key), find first or all entries with conditions.
     * @param mixed $id
     * @param array $options
     * @return Model
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     * @deprecated use find_all | find_first | find_last | find_by_primary_key
     */
    public static function find($id = 'all', array $options = []): self
    {
        $model = self::forge();

        // Find all entries
        if ('all' === $id)
        {
            $model->find_all($options);
        }
        // find first/last entry
        elseif ('first' === $id)
        {
            $model->find_first($options);
        }
        elseif ('last' === $id)
        {
            $model->find_last($options);
        }
        // find entry for given primary key
        else
        {
            $model->find_by_primary_key($id);
        }

        return $model;
    }

    public static function forge(): self
    {
        $model = static::class;

        return new $model();
    }

    /**
     * @param array $options
     * @return Model
     * @throws ModelException
     */
    public function find_all(array $options = []): self
    {
        $this->select($options['columns'] ?? null)
            ->from()
            ->apply_where_options($options)
            ->group_by($options['group_by'] ?? []);

        if (isset($options['order_by']))
        {
            $this->order_by($options['order_by'][0], $options['order_by'][1]);
        }

        return $this->execute();
    }

    /**
     * Query builder: This function set GROUP BY.
     * @param array $group_by
     * @return Model
     */
    public function group_by(array $group_by): self
    {
        if (!empty($group_by))
        {
            if (null === $this->group_by_statement)
            {
                $this->group_by_statement = ' ' . self::SQL_STATEMENT_GROUP_BY . ' ';
            }

            $total_group_by = count($group_by);

            $count = 1;

            foreach ($group_by as $value)
            {
                if ($total_group_by === 1)
                {
                    $this->group_by_statement .= $this->backticks($value);
                }
                elseif ($count < $total_group_by)
                {
                    $this->group_by_statement .= $this->backticks($value) . ', ';
                }
                else
                {
                    $this->group_by_statement .= $this->backticks($value);
                }
                $count++;
            }
        }

        return $this;
    }

    /**
     * This function returns the backticks for given value.
     * @param string $value
     * @return string
     */
    private function backticks(string $value): string
    {
        if (stripos($value, ' AS ') !== false)
        {
            [$col, $alias] = preg_split('/\s+AS\s+/i', $value);
            return $this->backticks(trim($col)) . ' AS ' . $alias;
        }

        if (preg_match('/^(.+)\s+([a-zA-Z0-9_]+)$/', $value, $m))
        {
            $col = trim($m[1]);
            $alias = trim($m[2]);

            return $this->backticks($col) . ' AS ' . $alias;
        }

        if (str_contains($value, 'MD5'))
        {
            preg_match('/MD5\((.*?)\)/i', $value, $m);

            if (!empty($m[1]) && !str_contains($m[1], '`'))
            {
                return 'MD5(`' . $m[1] . '`)';
            }

            return $value;
        }

        if (str_contains($value, '.'))
        {
            preg_match('/([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)/', $value, $m);
            return '`' . $m[1] . '`.`' . $m[2] . '`';
        }


        return '`' . $value . '`';
    }


    /**
     * @param array $options
     * @return Model
     */
    private function apply_where_options(array $options): self
    {
        if (isset($options['where']))
        {
            foreach ($options['where'] as $column => $value)
            {
                if (is_array($value))
                {
                    if (count($value) === 2)
                    {
                        $this->where($value[0], $value[1]);
                    }
                    elseif (count($value) === 3)
                    {
                        $this->where($value[0], $value[1], $value[2]);
                    }
                }
                else
                {
                    $this->where($column, $value);
                }
            }
        }

        return $this;
    }

    /**
     * Query builder: This function set the WHERE / AND condition.
     * Hint: If you want only to set a simple = condition, you can use only 2 parameters:
     * Example: ->where('column', 1)
     * SQL statement: WHERE column = 1 or AND column = 1
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float $value
     * @param bool $backticks
     * @param bool $formatValue
     * @return Model
     */
    public function where(string $column, $operator = null, $value = null, bool $backticks = true, bool $formatValue = true): self
    {
        if (\func_num_args() === 2)
        {
            $value = $operator;
            $operator = self::OPERATOR_EQUAL;
        }

        if ($operator === self::OPERATOR_IN)
        {
            $condition = $this->format_operator($value);
        }
        elseif ($formatValue)
        {
            $condition = $this->format_value($value);
        }
        else
        {
            $condition = $value;
        }

        $_column = $column;

        if ($backticks)
        {
            $_column = $this->backticks($column);
        }

        $where = $_column . ' ' . $operator;

        if (!$this->is_operator_null($operator))
        {
            $where .= ' ' . $condition;
        }

        $this->appendWhere($where);


        return $this;
    }

    /**
     * @param string|array $columns
     * @param string $search
     * @param bool $booleanMode
     * @param bool $withWildcards
     * @return $this
     */
    public function fulltext(string|array $columns, string $search, bool $booleanMode = true, bool $withWildcards = false): self
    {
        $cols = is_array($columns)
            ? implode(',', array_map([$this, 'backticks'], $columns))
            : $this->backticks($columns);

        if ($booleanMode && $withWildcards)
        {
            $search = implode(' ', array_map(static fn($w) => '+' . $w . '*',
                preg_split('/\s+/', trim($search))
            ));
        }

        $mode = $booleanMode ? ' IN BOOLEAN MODE' : '';
        $expr = 'MATCH('.$cols.') AGAINST (' . $this->format_value($search) . $mode . ')';

        $this->appendWhere($expr);

        return $this;
    }

    /**
     * @param string|array $columns
     * @param string $search
     * @param bool $booleanMode
     * @param bool $withWildcards
     * @return $this
     */
    public function fulltextWithScore(string|array $columns, string $search, bool $booleanMode = true, bool $withWildcards = false): self
    {
        $cols = is_array($columns)
            ? implode(',', array_map([$this, 'backticks'], $columns))
            : $this->backticks($columns);

        if ($booleanMode && $withWildcards)
        {
            $search = implode(' ', array_map(static fn($w) => '+' . $w . '*',
                preg_split('/\s+/', trim($search))
            ));
        }

        $mode = $booleanMode ? ' IN BOOLEAN MODE' : '';
        $expr = 'MATCH('.$cols.') AGAINST (' . $this->format_value($search) . $mode . ')';


        if (empty($this->select_statement))
        {
            $this->select_statement = '*';
        }

        $this->select_statement .= ', ' . $expr . ' AS relevance';

        $this->appendWhere($expr);

        return $this;
    }


    public function and(): self
    {
        $this->next_where_boolean = self::SQL_STATEMENT_AND;
        return $this;
    }

    public function or(): self
    {
        $this->next_where_boolean = self::SQL_STATEMENT_OR;
        return $this;
    }


    /**
     * This function format value, if operator is IN operator
     * @param string $value
     * @return string
     */
    private function format_operator(string $value): string
    {
        return '(' . $value . ')';
    }

    /**
     * This function format the given value to numeric or string.
     * @param int|float|string $value
     * @return string|int
     */
    private function format_value($value)
    {
        if (is_numeric($value))
        {
            return $value;
        }

        return '"' . $value . '"';
    }

    /**
     * Query builder: This function appends the table to select FROM.
     * @param null|string $table_name
     * @param string|null $alias
     * @return Model
     * @throws ModelException
     */
    public function from(?string $table_name = null, ?string $alias = null): self
    {
        if (null === $table_name)
        {
            $this->from_statement = ' ' . self::SQL_COMMAND_FROM . ' ' . $this->backticks($this->table());
        }
        else
        {
            $this->from_statement = ' ' . self::SQL_COMMAND_FROM . ' ' . $this->backticks($table_name);

            if (null !== $alias)
            {
                $this->from_statement .= ' ' . $alias;
            }
        }

        return $this;
    }

    /**
     * This method returns the table name for current model.
     * @return string
     * @throws ModelException
     */
    public function table(): string
    {
        if (is_string($this->table_name) && $this->table_name !== '')
        {
            return $this->table_name;
        }

        throw new ModelException(__CLASS__ . '::' . __FUNCTION__ . '(): self ' . $this->class . ' has no Table property!');
    }

    public function set_table_alias(string $alias): self
    {
        $this->table_alias = $alias;

        return $this;
    }

    protected function get_table_alias(): ?string
    {
        return $this->table_alias;
    }

    /**
     * Query builder: This function set the SELECT statement.
     * @param null|array|string $columns
     * @return Model
     */
    public function select($columns = null): self
    {
        if (empty($columns))
        {
            $this->select_statement .= '*';
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
                        $this->select_statement .= $this->backticks($value);
                    }
                    elseif ($count < $total_columns)
                    {
                        $this->select_statement .= $this->backticks($value) . ', ';
                    }
                    else
                    {
                        $this->select_statement .= $this->backticks($value);
                    }
                    $count++;
                }
            }
            else
            {
                $this->select_statement = $columns;
            }
        }

        return $this;
    }

    public function distinct(bool $value = true): self
    {
        $this->select_distinct = $value;
        return $this;
    }

    public function reset_select(): self
    {
        $this->select_statement = null;

        return $this;
    }

    public function order_by(string $column, string $direction = self::SQL_ORDER_BY_ASC, bool $backticks = true): self
    {

        if (null === $this->order_by_statement)
        {
            $separator = '';
            $order_by_sql = self::SQL_STATEMENT_ORDER_BY;
        }
        else
        {
            $separator = ',';
            $order_by_sql = '';
        }

        $_column = $column;

        if ($backticks)
        {
            $_column = $this->backticks($column);
        }

        $this->order_by_statement .= $separator . ' ' . $order_by_sql . ' ' . $_column . ' ' . $direction;

        return $this;
    }

    /**
     * Query builder: This function execute the compiled statement.
     * @param string $option
     * @return Model
     * @throws ModelException
     */
    public function execute(string $option = self::EXECUTE_MODE_READ): self
    {
        if ($option === self::EXECUTE_MODE_WRITE)
        {
            Db::write($this->compile(), $this->connection);

            return $this;
        }

        $this->result = Db::read($this->compile(), $this->connection);

        return $this;
    }

    /**
     * Query builder: This method build the full select query.
     * @return null|string
     * @throws ModelException
     */
    public function compile(): ?string
    {
        if (null === $this->query_statement)
        {
            $this->query_statement = self::SQL_COMMAND_SELECT . ' ';

            if ($this->select_distinct)
            {
                $this->query_statement .= 'DISTINCT ';
            }

            if (null === $this->from_statement)
            {
                $this->from();
            }

            if ($this->get_table_alias() !== null)
            {
                $this->from_statement .= ' ' . $this->get_table_alias();
            }

            $this->query_statement .= $this->select_statement . $this->from_statement . ' ';

            if (null !== $this->join_statement)
            {
                $this->query_statement .= $this->join_statement . ' ';
            }

            if (!empty($this->where_statement))
            {
                foreach ($this->where_statement as $value)
                {
                    $this->query_statement .= $value;
                }
            }

            if (null !== $this->group_by_statement)
            {
                $this->query_statement .= $this->group_by_statement;
            }

            if (null !== $this->order_by_statement)
            {
                $this->query_statement .= $this->order_by_statement;
            }

            if (null !== $this->limit_statement)
            {
                $this->query_statement .= $this->limit_statement;
            }
        }

        return $this->query_statement;
    }

    /**
     * @param array $options
     * @return Model
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     */
    public function find_first(array $options = []): self
    {
        return $this->select($options['columns'] ?? null)
            ->from()
            ->apply_where_options($options)
            ->group_by($options['group_by'] ?? [])
            ->order_by($this->primary_key())
            ->limit(1)
            ->execute()
            ->prepare_find_result();
    }

    /**
     * @return $this
     */
    public function prepare_find_result(): self
    {
        $find_result = $this->get_result();

        if (null !== $find_result && false !== $find_result)
        {
            $this->result = $this->result[0];
            $this->set_id($this->result[$this->primary_key()]);
        }

        return $this;
    }

    /**
     * @return false|array|\stdClass|null
     */
    public function get_result()
    {
        return $this->result;
    }

    /**
     * This function set the data id.
     * @param mixed $id
     */
    private function set_id($id): void
    {
        $this->_id = $id;
    }

    /**
     * Query builder: This function set the limit and offset.
     * @param int $limit
     * @param int $offset
     * @return Model
     * @throws ModelInvalidArgumentException
     */
    public function limit(int $limit, int $offset = 0): self
    {
        if ($limit === 0 && $offset === 0)
        {
            throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Required parameter not given!');
        }

        $this->limit_statement = ' ' . self::SQL_CLAUSE_LIMIT . ' ' . $offset . ', ' . $limit;

        return $this;
    }

    /**
     * @return string
     */
    public function primary_key(): string
    {
        return $this->primary_key;
    }

    /**
     * @param array $options
     * @return Model
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     */
    public function find_last(array $options = []): self
    {
        return $this->select($options['columns'] ?? null)
            ->from()
            ->apply_where_options($options)
            ->group_by($options['group_by'] ?? [])
            ->order_by($this->primary_key(), 'DESC')
            ->limit(1)
            ->execute()
            ->prepare_find_result();
    }

    /**
     * @param string|int $id
     * @return Model
     * @throws ModelException
     */
    public function find_by_primary_key($id): self
    {
        return $this->select()
            ->from()
            ->where($this->primary_key(), $id)
            ->execute()
            ->prepare_find_result();
    }

    /**
     * Query builder: This function appends the table to JOIN from.
     * @param string $table
     * @param string $direction
     * @param null|string $alias
     * @return Model
     */
    public function join(string $table, string $direction = self::JOIN_LEFT, ?string $alias = null): self
    {
        if (!empty($table))
        {

            $this->join_statement .= $direction . ' ' . self::SQL_CLAUSE_JOIN . ' ' . $this->backticks($table);

            if (null !== $alias)
            {
                $this->join_statement .= ' ' . $alias . ' ';
            }
        }

        return $this;
    }

    /**
     * Query builder: This function appends the table to JOIN ON.
     * @param string $column1
     * @param string $column2
     * @return Model
     * @throws ModelInvalidArgumentException
     */
    public function on(string $column1, string $column2): self
    {
        if (!empty($column1) && !empty($column2))
        {
            if (strpos($column1, '.') === false)
            {
                throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Column1 value "' . $column1 . '" must have table name and column name separated with a dot! Example: "table_name.row"');
            }

            if (strpos($column2, '.') === false)
            {
                throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Column2 value "' . $column2 . '" must have table name and column name separated with a dot! Example: "table_name.row"');
            }

            $this->join_statement .= self::SQL_CLAUSE_ON . ' ' . $this->backticks($column1) . ' = ' . $this->backticks($column2) . ' ';

            return $this;
        }

        throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Column1 and Column2 must have table name and column name separated with a dot! Example: "table_name.row"');
    }

    /**
     * Query builder: This function appends the JOIN
     * @param string $column1
     * @param string $column2
     * @return Model
     * @throws ModelInvalidArgumentException
     */
    public function or_on(string $column1, string $column2): self
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

            $this->join_statement .= ' OR ' . $this->backticks($column1) . ' = ' . $this->backticks($column2) . ' ';

            return $this;
        }

        throw new ModelInvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '(): Column1 and Column2 must have table name and column name separated with a dot! Example: "table_name.row"');
    }

    /**
     * Query builder: This function set the OR condition.
     * Hint: If you want only to set a simple = condition, you can use only 2 parameters:
     * Example: ->or_where('column', 1)
     * SQL statement: OR column = 1
     * @param string $column
     * @param string|null $operator
     * @param string|int|null $value
     * @param bool $backticks
     * @return Model
     */
    public function or_where(string $column, ?string $operator = null, $value = null, bool $backticks = true): self
    {
        if (func_num_args() === 2)
        {
            $value = $operator;
            $operator = self::OPERATOR_EQUAL;
        }

        if ($operator === self::OPERATOR_IN)
        {
            $condition = $this->format_operator($value);
        }
        else
        {
            $condition = $this->format_value($value);
        }

        $_column = $column;

        if ($backticks)
        {
            $_column = $this->backticks($column);
        }

        $where = $_column . ' ' . $operator;

        if (!$this->is_operator_null($operator))
        {
            $where .= ' ' . $condition;
        }

        $this->where_statement[] = ' ' . self::SQL_STATEMENT_OR . ' ' . $where;

        return $this;
    }

    /**
     * Query builder: This function set the OR open condition.
     * @param string $column
     * @param string|int|null $operator
     * @param mixed|null $value
     * @param bool $backticks
     * @return Model
     */
    public function or_where_open(string $column, $operator = null, $value = null, bool $backticks = true): self
    {
        if (func_num_args() === 2)
        {
            return $this->where_open_by_condition(self::SQL_STATEMENT_OR, $column, null, $operator, $backticks);
        }

        return $this->where_open_by_condition(self::SQL_STATEMENT_OR, $column, $operator, $value, $backticks);
    }

    /**
     * @param string $where_condition
     * @param string $column
     * @param string|int|null $operator
     * @param mixed|null $value
     * @param bool $backticks
     * @return Model
     */
    public function where_open_by_condition(string $where_condition, string $column, $operator, $value = null, bool $backticks = true): self
    {
        if ($operator === null)
        {
            $operator = self::OPERATOR_EQUAL;
        }

        if ($operator === self::OPERATOR_IN)
        {
            $condition = $this->format_operator($value);
        }
        else
        {
            $condition = $this->format_value($value);
        }

        $_column = $column;
        if ($backticks)
        {
            $_column = $this->backticks($column);
        }

        $where = $_column . ' ' . $operator;

        if (!$this->is_operator_null($operator))
        {
            $where .= ' ' . $condition;
        }

        $this->where_statement[] = ' ' . $where_condition . ' ' . $this->_open() . $where;

        return $this;
    }

    private function _open(): string
    {
        return '(';
    }

    public function or_where_close(): self
    {
        $this->where_close();

        return $this;
    }

    public function where_close(): self
    {
        $this->where_statement[] = $this->_close();

        return $this;
    }

    private function _close(): string
    {
        return ')';
    }

    /**
     * Query builder: This function set the AND open condition.
     * @param string $column
     * @param mixed|null $operator
     * @param mixed|null $value
     * @param bool $backticks
     * @return Model
     */
    public function and_where_open(string $column, $operator, $value = null, bool $backticks = true): self
    {
        if (func_num_args() === 2)
        {
            return $this->where_open_by_condition(self::SQL_STATEMENT_AND, $column, null, $operator, $backticks);
        }

        return $this->where_open_by_condition(self::SQL_STATEMENT_AND, $column, $operator, $value, $backticks);
    }

    public function and_where_close(): self
    {
        $this->where_close();

        return $this;
    }

    /**
     * Query builder: This function set the AND open condition.
     * Example: ->where_open()
     * SQL statement: AND (
     * @return Model
     */
    public function where_open(): self
    {
        $this->where_statement[] = ' ' . self::SQL_STATEMENT_AND . ' ' . $this->_open();

        return $this;
    }

    public function query($query = null): self
    {
        if (null !== $query)
        {
            $this->query_statement = $query;
        }

        return $this;
    }

    /**
     * Query builder: This function return the result as array.
     * @return array|null|bool
     */
    public function as_array()
    {
        if (!$this->result)
        {
            return false;
        }

        if (!is_array($this->result))
        {
            return (array)$this->result;
        }

        return $this->result;
    }

    /**
     * Query builder: This function return the result as object.
     * @return null|object|bool
     */
    public function as_object()
    {
        if (!$this->result)
        {
            return false;
        }

        if (!is_object($this->result))
        {
            return (object)$this->result;
        }

        return $this->result;
    }

    /**
     * Query builder: This function set property for INSERT or UPDATE data.
     * @param string|array $property
     * @param mixed $value
     * @return Model
     * @throws ModelException
     * @throws ModelPrimaryKeyException
     * @throws ModelPropertyException
     */
    public function set($property, $value = null): self
    {
        if (is_array($property))
        {
            foreach ($property as $key => $property_value)
            {
                $this->set($key, $property_value);
            }
        }
        else
        {
            $this->set_data($property, $value);
        }

        return $this;
    }

    /**
     * This function set the data for the model properties.
     * @param string $property
     * @param mixed $value
     * @throws ModelPrimaryKeyException
     * @throws ModelPropertyException
     */
    private function set_data(string $property, $value): void
    {
        if ($property === $this->primary_key())
        {
            throw new ModelPrimaryKeyException('Primary key on model ' . $this->class . ' cannot be changed.');
        }

        if (!$this->property_exists($property))
        {
            throw new ModelPropertyException(__CLASS__ . '::' . __FUNCTION__ . '(): self ' . $this->class . ' has no property "' . $property . '"!');
        }
        $this->data[$property] = $value;
    }

    /**
     * This function check if given property exist in model.
     * @param string $property
     * @return bool
     */
    public function property_exists(string $property): bool
    {
        return array_key_exists($property, $this->properties());
    }

    /**
     * This method returns the properties for current model.
     * Please note: you must set the properties in your model.
     * @param bool $keys_only
     * @return array
     */
    public function properties(bool $keys_only = false): array
    {
        foreach ($this->properties as $key => $p)
        {
            if (is_string($p))
            {
                unset($this->properties[$key]);
                $this->properties[$p] = [];
            }
        }

        if ($keys_only)
        {
            return array_keys($this->properties);
        }

        return $this->properties;
    }

    /**
     * Query builder: This function INSERT or UPDATE data into database.
     * @return bool|int
     * @throws ModelException
     * @throws ModelPropertyException
     */
    public function save()
    {
        if (!is_null($this->get_id()))
        {
            return $this->update($this->get_id(), $this->get_data());
        }

        return $this->insert($this->get_data());
    }

    /**
     * This function get the data id.
     * @return null|mixed
     */
    private function get_id()
    {
        return $this->_id;
    }

    /**
     * Query builder: This function UPDATE data in database.
     * @param mixed $id
     * @param array $data
     * @return bool
     * @throws ModelException
     * @throws ModelPropertyException
     */
    public function update($id, array $data = []): bool
    {
        if (empty($data))
        {
            return false;
        }

        $this->has_properties($data);

        $sql = self::SQL_COMMAND_UPDATE . '
            ' . $this->table() . ' ' . self::SQL_COMMAND_SET . ' ' . $this->prepare_update($data) . ' ' . self::SQL_STATEMENT_WHERE . ' ' . $this->backticks($this->primary_key()) . ' = ' . $id;

        return Db::write($sql, $this->connection);
    }

    /**
     * @param array $data
     * @throws ModelPropertyException
     */
    private function has_properties(array $data): void
    {
        foreach ($data as $key => $value)
        {
            if (!$this->property_exists($key))
            {
                throw new ModelPropertyException(__CLASS__ . '::' . __FUNCTION__ . '(): self ' . $this->class . ' has no property "' . $key . '"!');
            }
        }
    }

    /**
     * This function prepare data for UPDATE.
     * @param array $array
     * @return bool|string
     */
    public function prepare_update(array $array)
    {
        if (empty($array))
        {
            return false;
        }

        $columns = '';

        foreach ($array as $key => $value)
        {
            if ($value === null)
            {
                $columns .= ' ' . $this->backticks(preg_replace('/[^a-z_A-Z0-9]/', '', $key)) . ' = NULL,';

                continue;
            }

            $columns .= ' ' . $this->backticks(preg_replace('/[^a-z_A-Z0-9]/', '', $key)) . ' = \'' . $this->escape($value) . '\',';
        }

        return rtrim($columns, ',');
    }

    /**
     * @param string|int $value
     * @return string
     */
    public function escape($value): string
    {
        return Db::escape((string)$value, $this->connection);
    }

    /**
     * This function get the data for the model properties.
     * @return array
     */
    private function get_data(): array
    {
        return $this->data;
    }

    /**
     * Query builder: This function INSERT data into database.
     * @param array $data
     * @return bool|int
     * @throws ModelException
     * @throws ModelPropertyException
     */
    public function insert(array $data = [])
    {
        if (empty($data))
        {
            return false;
        }

        $this->has_properties($data);
        $insert_data = $this->prepare_insert($data);

        $sql = self::SQL_COMMAND_INSERT . '
                    ' . $this->table() . '
                    ' . $insert_data[0] . '
                ' . self::SQL_COMMAND_VALUES . '
                    ' . $insert_data[1] . '
                ';

        return Db::insert($sql, $this->connection);
    }

    /**
     * This function prepare data for INSERT.
     * @param array $array
     * @return bool|array
     */
    public function prepare_insert(array $array)
    {
        if (empty($array))
        {
            return false;
        }

        $columns = $this->_open();
        $columns_data = $this->_open();

        foreach ($array as $key => $value)
        {
            $columns .= $this->backticks($key) . ',';

            if ($value === null)
            {
                $columns_data .= 'NULL,';

                continue;
            }

            $columns_data .= '"' . $this->escape($value) . '",';
        }

        $columns = rtrim($columns, ',');
        $columns .= $this->_close();

        $columns_data = rtrim($columns_data, ',');
        $columns_data .= $this->_close();

        return [$columns, $columns_data];
    }

    /**
     * Query builder: This function DELETE data from database.
     * @param mixed $id
     * @return bool
     * @throws ModelException
     */
    public function delete($id): bool
    {
        $sql = self::SQL_COMMAND_DELETE . ' ' . self::SQL_COMMAND_FROM . '
                ' . $this->table() . '
            ' . self::SQL_STATEMENT_WHERE . '
                ' . $this->backticks($this->primary_key()) . ' = ' . $id . '
        ';

        return Db::write($sql, $this->connection);
    }

    public function open(): self
    {
        $this->where_statement[] = '(';

        return $this;
    }

    public function close(): self
    {
        $this->where_statement[] = ')';

        return $this;
    }

    /**
     * This function returns the data value for current model.
     * @param string $property
     * @return bool|mixed
     */
    public function data_value(string $property)
    {
        $model_properties = $this->properties();
        $data_type = $this->data_type($property);

        if (!$data_type || (!isset($model_properties[$property]['length']) && !isset($model_properties[$property]['set'])))
        {
            return false;
        }

        $data_value = (isset($model_properties[$property]['length'])) ? 'length' : 'set';

        return $model_properties[$property][$data_value];
    }

    /**
     * This function returns the data type for current model.
     * @param string $property
     * @return bool|mixed
     */
    public function data_type(string $property)
    {
        $model_properties = $this->properties();

        if (!is_array($model_properties[$property]) || !isset($model_properties[$property]['data_type']))
        {
            return false;
        }

        return $model_properties[$property]['data_type'];
    }

    /**
     * This function returns the field names from db.
     * @return array
     * @throws ModelException
     */
    public function field_names(): array
    {
        $data_from_db = Db::field_names($this->compile(), $this->connection);

        if (!empty($data_from_db))
        {
            $this->set_db_schema($data_from_db);
        }

        return $this->get_db_schema();
    }

    /**
     * @param array $schema
     * @return void
     */
    private function set_db_schema(array $schema): void
    {
        $this->db_schema = $schema;
    }

    /**
     * @return array
     */
    private function get_db_schema(): array
    {
        return $this->db_schema;
    }

    public function has_result(): bool
    {
        return false !== $this->result;
    }

    /**
     * Query builder: This method build the full select query.
     * @return null|string
     * @throws ModelException
     */
    public function get_count_compile(): ?string
    {
        $count_query_statement = self::SQL_COMMAND_SELECT . ' ';

        $this->from();

        if ($this->get_table_alias() !== null)
        {
            $this->from_statement .= ' ' . $this->get_table_alias();
        }

        $count_query_statement .= 'COUNT(*) AS count' . $this->from_statement . ' ';

        if (null !== $this->join_statement)
        {
            $count_query_statement .= $this->join_statement . ' ';
        }

        if (!empty($this->where_statement))
        {
            foreach ($this->where_statement as $value)
            {
                $count_query_statement .= $value;
            }
        }

        if (null !== $this->group_by_statement)
        {
            $count_query_statement .= $this->group_by_statement;
        }

        return $count_query_statement;
    }

    /**
     * @return $this
     * @throws ModelException
     */
    public function get_count(): self
    {
        $data_from_db = Db::read($this->get_count_compile(), $this->connection);

        if ($data_from_db !== false)
        {
            $this->result = $data_from_db;
        }
        else
        {
            $this->result = false;
        }

        return $this;
    }

    /**
     * @param string $column_name
     * @return string|int|bool|null
     * @throws ModelException
     */
    public function get_default(string $column_name)
    {
        if (!$this->property_exists($column_name))
        {
            throw new ModelException(sprintf('column name does not exist on Model: %s::%s', __CLASS__, $column_name));
        }

        if (!isset($this->properties[$column_name]['default']))
        {
            throw new ModelException(sprintf('default definition on %s does not exist', $column_name));
        }

        return $this->properties[$column_name]['default'];
    }

    /**
     * @param int|string $operator
     */
    private function is_operator_null($operator): bool
    {
        return \in_array($operator, [self::OPERATOR_IS_NULL, self::OPERATOR_IS_NOT_NULL], true);
    }

    protected function appendWhere(string $condition): void
    {
        if (empty($this->where_statement))
        {
            $this->where_statement[] = self::SQL_STATEMENT_WHERE . ' ' . $condition;
        }
        else
        {
            $this->where_statement[] = ' ' . $this->next_where_boolean . ' ' . $condition;
        }

        $this->next_where_boolean = self::SQL_STATEMENT_AND;
    }
}
