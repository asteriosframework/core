<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Contracts\Orm\OrmQueryBuilderInterface;
use Asterios\Core\Contracts\Orm\OrmSqlFormatterInterface;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\ModelException;
use Asterios\Core\Exception\ModelInvalidArgumentException;
use Asterios\Core\Exception\ModelPrimaryKeyException;
use Asterios\Core\Exception\ModelPropertyException;
use Asterios\Core\Orm\OrmMetadata;
use Asterios\Core\Orm\OrmQueryBuilder;
use Asterios\Core\Orm\OrmSqlFormatter;

class Model
{
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
    protected string $connection = 'default';
    protected $class;
    protected string $table_name = '';
    protected ?string $table_alias = null;
    protected string $primary_key = '';
    protected array $properties = [];
    protected array $update = [];

    protected array|bool $result = false;
    protected array $data = [];
    protected $_id;
    protected $db_schema = [];

    protected OrmQueryBuilderInterface $queryBuilder;
    protected OrmSqlFormatterInterface $formatter;

    /**
     * @throws ModelException
     */
    public function __construct(?OrmSqlFormatterInterface $formatter = null)
    {
        $this->formatter = $formatter ?? new OrmSqlFormatter();

        $this->rebuildQueryBuilder();

        $this->reset();
    }

    public function reset(): self
    {
        if (isset($this->queryBuilder))
        {
            $this->queryBuilder->reset();
        }

        return $this;
    }

    /**
     * @return void
     * @throws ModelException
     */
    private function rebuildQueryBuilder(): void
    {
        $metadata = new OrmMetadata(
            $this->table(),
            $this->table_alias,
            $this->connection
        );

        $this->queryBuilder = new OrmQueryBuilder(
            $metadata,
            $this->formatter
        );
    }

    /**
     * This method returns the table name for current model.
     * @return string
     * @throws ModelException
     */
    public function table(): string
    {
        if ($this->table_name !== '')
        {
            return $this->table_name;
        }

        throw new ModelException(__CLASS__ . '::' . __FUNCTION__ . '(): self ' . $this->class . ' has no Table property!');
    }

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
     * @param array $groupBy
     * @return Model
     * @deprecated Use groupBy() instead
     */
    public function group_by(array $groupBy): self
    {
        $this->queryBuilder->groupBy($groupBy);

        return $this;
    }

    /**
     * @param array $groupBy
     * @return $this
     */
    public function groupBy(array $groupBy): self
    {
        $this->queryBuilder->groupBy($groupBy);

        return $this;
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
    public function where(
        string $column,
        string|int|null $operator = null,
        string|int|float $value = null,
        bool $backticks = true,
        bool $formatValue = true
    ): self
    {
        $this->queryBuilder->where($column, $operator, $value, $backticks, $formatValue);

        return $this;
    }

    /**
     * Query builder: This function appends the table to select FROM.
     * @param null|string $tableName
     * @param string|null $alias
     * @return Model
     * @throws ModelException
     */
    public function from(?string $tableName = null, ?string $alias = null): self
    {
        $this->queryBuilder->from($tableName, $alias);

        return $this;
    }

    /**
     * Query builder: This function set the SELECT statement.
     * @param null|array|string $columns
     * @return Model
     */
    public function select(null|array|string $columns = null): self
    {
        $this->queryBuilder->select($columns);

        return $this;
    }

    /**
     * @param string $column
     * @param string $direction
     * @param bool $backticks
     * @return $this
     * @deprecated Use orderBy() instead
     */
    public function order_by(string $column, string $direction = 'ASC', bool $backticks = true): self
    {
        $this->queryBuilder->orderBy($column, $direction, $backticks);

        return $this;
    }

    /**
     * @param string $column
     * @param string $direction
     * @param bool $backticks
     * @return $this
     */
    public function orderBy(string $column, string $direction = 'ASC', bool $backticks = true): self
    {
        $this->queryBuilder->orderBy($column, $direction, $backticks);

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
        return $this->queryBuilder->compile();
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
     * @return string
     */
    public function primary_key(): string
    {
        return $this->primary_key;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return Model
     * @throws ModelInvalidArgumentException
     */
    public function limit(int $limit, int $offset = 0): self
    {
        $this->queryBuilder->limit($limit, $offset);

        return $this;
    }

    /**
     * @param array $options
     * @return Model
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     * @deprecated Use findLast() instead
     */
    public function find_last(array $options = []): self
    {
        return $this->findLast($options);
    }

    /**
     * @param array $options
     * @return self
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     */
    public function findLast(array $options = []): self
    {
        return $this->select($options['columns'] ?? null)
            ->from()
            ->apply_where_options($options)
            ->groupBy($options['group_by'] ?? [])
            ->orderBy($this->primary_key(), 'DESC')
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
     * @param string|array $columns
     * @param string $search
     * @param bool $booleanMode
     * @param bool $withWildcards
     * @return $this
     */
    public function fulltext(string|array $columns, string $search, bool $booleanMode = true, bool $withWildcards = false): self
    {
        $this->queryBuilder->fulltext($columns, $search, $booleanMode, $withWildcards);

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
        $this->queryBuilder->fulltextWithScore($columns, $search, $booleanMode, $withWildcards);

        return $this;
    }

    public function distinct(bool $value = true): self
    {
        $this->queryBuilder->distinct($value);

        return $this;
    }

    /**
     * Query builder: This function appends the table to JOIN from.
     * @param string $table
     * @param string $direction
     * @param null|string $alias
     * @return Model
     */
    public function join(string $table, string $direction = 'LEFT', ?string $alias = null): self
    {
        $this->queryBuilder->join($table, $direction, $alias);

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
        $this->queryBuilder->on($column1, $column2);

        return $this;
    }

    /**
     * Query builder: This function appends the JOIN
     * @param string $column1
     * @param string $column2
     * @return Model
     * @throws ModelInvalidArgumentException
     * @deprecated Use orOn() instead
     */
    public function or_on(string $column1, string $column2): self
    {
        $this->queryBuilder->orOn($column1, $column2);

        return $this;
    }

    /**
     * Query builder: This function appends the JOIN
     * @param string $column1
     * @param string $column2
     * @return Model
     * @throws ModelInvalidArgumentException
     */
    public function orOn(string $column1, string $column2): self
    {
        $this->queryBuilder->orOn($column1, $column2);

        return $this;
    }

    /**
     * Query builder: This function set the OR condition.
     * Hint: If you want only to set a simple = condition, you can use only 2 parameters:
     * Example: ->or_where('column', 1)
     * SQL statement: OR column = 1
     * @param string $column
     * @param string|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return Model
     * @deprecated Use orWhere() instead
     */
    public function or_where(string $column, ?string $operator = null, string|int|null $value = null, bool $backticks = true): self
    {
        $this->queryBuilder->orWhere($column, $operator, $value, $backticks);

        return $this;
    }

    /**
     * @param string $column
     * @param string|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return $this
     */
    public function orWhere(string $column, ?string $operator = null, string|int|float|null $value = null, bool $backticks = true): self
    {
        $this->queryBuilder->orWhere($column, $operator, $value, $backticks);

        return $this;
    }

    /**
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return Model
     * @deprecated Use orWhereOpen() instead
     */
    public function or_where_open(string $column, string|int|null $operator = null, string|int|float|null $value = null, bool $backticks = true): self
    {
        $this->queryBuilder->orWhereOpen($column, $operator, $value, $backticks);

        return $this;
    }

    /**
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return Model
     */
    public function orWhereOpen(string $column, string|int|null $operator = null, string|int|float|null $value = null, bool $backticks = true): self
    {
        $this->queryBuilder->orWhereOpen($column, $operator, $value, $backticks);

        return $this;
    }

    /**
     * @param string $where_condition
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return Model
     * @deprecated Use whereOpenByCondition() instead
     */
    public function where_open_by_condition(
        string $where_condition,
        string $column,
        string|int|null $operator,
        string|int|float|null $value = null,
        bool $backticks = true
    ): self
    {
        $this->queryBuilder->whereOpenByCondition($where_condition, $column, $operator, $value, $backticks);

        return $this;
    }

    /**
     * @param string $where_condition
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return $this
     */
    public function whereOpenByCondition(
        string $where_condition,
        string $column,
        string|int|null $operator,
        string|int|float|null $value = null,
        bool $backticks = true
    ): self
    {
        $this->queryBuilder->whereOpenByCondition($where_condition, $column, $operator, $value, $backticks);

        return $this;
    }

    /**
     * @return $this
     * @deprecated Use orWhereClose() instead
     */
    public function or_where_close(): self
    {
        $this->queryBuilder->orWhereClose();

        return $this;
    }

    /**
     * @return $this
     */
    public function orWhereClose(): self
    {
        $this->queryBuilder->whereClose();

        return $this;
    }

    /**
     * @return $this
     */
    public function whereClose(): self
    {
        $this->queryBuilder->whereClose();

        return $this;
    }

    /**
     * Query builder: This function set the AND open condition.
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return Model
     * @deprecated Use andWhereOpen() instead
     */
    public function and_where_open(string $column, string|int|null $operator, string|int|float|null $value = null, bool $backticks = true): self
    {
        $this->queryBuilder->andWhereOpen($column, $operator, $value, $backticks);

        return $this;
    }

    /**
     * Query builder: This function set the AND open condition.
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return Model
     */
    public function andWhereOpen(string $column, string|int|null $operator, string|int|float|null $value = null, bool $backticks = true): self
    {
        $this->queryBuilder->andWhereOpen($column, $operator, $value, $backticks);

        return $this;
    }

    public function and_where_close(): self
    {
        $this->where_close();

        return $this;
    }

    /**
     * @return $this
     * @deprecated Use whereClose() instead
     */
    public function where_close(): self
    {
        $this->queryBuilder->whereClose();

        return $this;
    }

    /**
     * Query builder: This function set the AND open condition.
     * Example: ->where_open()
     * SQL statement: AND (
     * @return Model
     * @deprecated Use whereOpen() instead
     */
    public function where_open(): self
    {
        $this->queryBuilder->whereOpen();

        return $this;
    }

    /**
     * Query builder: This function set the AND open condition.
     * Example: ->whereOpen()
     * SQL statement: AND (
     * @return Model
     */
    public function whereOpen(): self
    {
        $this->queryBuilder->whereOpen();

        return $this;
    }

    public function and(): self
    {
        $this->queryBuilder->and();

        return $this;
    }

    public function or(): self
    {
        $this->queryBuilder->or();

        return $this;
    }

    public function query($query = null): self
    {
        $this->queryBuilder->query($query);

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
     * @throws ModelException|ModelPropertyException|ConfigLoadException
     */
    public function update($id, array $data = []): bool
    {
        if (empty($data))
        {
            return false;
        }

        $this->has_properties($data);

        $sql = 'UPDATE ' . $this->table() . ' SET ' . $this->prepare_update($data) . ' WHERE ' . $this->backticks($this->primary_key()) . ' = ' . $id;

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
     * @throws ConfigLoadException
     */
    public function escape($value): string
    {
        return Db::escape((string)$value, $this->connection);
    }

    /**
     * @return array
     */
    private function get_data(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return false|int|string
     * @throws ModelException
     * @throws ModelPropertyException
     * @throws ConfigLoadException
     */
    public function insert(array $data = []): false|int|string
    {
        if (empty($data))
        {
            return false;
        }

        $this->has_properties($data);
        $insert_data = $this->prepare_insert($data);

        $sql = 'INSERT INTO
                    ' . $this->table() . '
                    ' . $insert_data[0] . '
                         VALUES
                    ' . $insert_data[1] . '
                ';

        return Db::insert($sql, $this->connection);
    }

    /**
     * This function prepare data for INSERT.
     * @param array $array
     * @return bool|array
     */
    public function prepare_insert(array $array): bool|array
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

    private function _open(): string
    {
        return '(';
    }

    private function _close(): string
    {
        return ')';
    }

    /**
     * @param string|int $id
     * @return bool
     * @throws ModelException
     * @throws ConfigLoadException
     */
    public function delete(string|int $id): bool
    {
        $sql = 'DELETE FROM ' . $this->table() . ' WHERE ' . $this->backticks($this->primary_key()) . ' = ' . $id . '
        ';

        return Db::write($sql, $this->connection);
    }

    /**
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
     * @throws ModelException|ConfigLoadException
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
     * @return $this
     * @throws ModelException|ConfigLoadException
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
     * @return null|string
     * @throws ModelException
     */
    public function get_count_compile(): ?string
    {
        return $this->queryBuilder->getCountCompile();
    }

    public function get_table_alias(): ?string
    {
        return $this->table_alias;
    }

    /**
     * @param string $alias
     * @return $this
     * @throws ModelException
     */
    public function set_table_alias(string $alias): self
    {
        $this->table_alias = $alias;

        $this->rebuildQueryBuilder();

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
}
