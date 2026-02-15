<?php /** @noinspection PhpUnused */

declare(strict_types=1);

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
    public const string EXECUTE_MODE_READ = 'read';
    public const string EXECUTE_MODE_WRITE = 'write';
    protected string $connection = 'default';
    protected string $tableName = '';
    protected ?string $tableAlias = null;
    protected string $primaryKey = '';
    protected array $properties = [];
    protected array $update = [];

    protected array|bool $result = false;
    protected array $data = [];
    protected int|string $_id;
    protected array $dbSchema = [];

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
            $this->tableAlias,
            $this->connection
        );

        $this->queryBuilder = new OrmQueryBuilder(
            $metadata,
            $this->formatter
        );
    }

    /**
     * @return string
     * @throws ModelException
     */
    public function table(): string
    {
        if ($this->tableName !== '')
        {
            return $this->tableName;
        }

        throw new ModelException(__CLASS__ . '::' . __FUNCTION__ . '(): self ' . static::class . ' has no Table property!');
    }

    /**
     * @param int|string $id
     * @param array $options
     * @return Model
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     * @throws ConfigLoadException
     * @deprecated use findAll() | findFirst() | findLast() | findByPrimaryKey() instead
     */
    public static function find(int|string $id = 'all', array $options = []): self
    {
        $model = self::forge();

        match ($id) {
            'all' => $model->findAll($options),
            'first' => $model->findFirst($options),
            'last' => $model->findLast($options),
            default => $model->findByPrimaryKey($id),
        };

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
     * @throws ConfigLoadException
     * @deprecated Use findAll() instead
     */
    public function find_all(array $options = []): self
    {
        return $this->findAll($options);
    }

    /**
     * @param array $options
     * @return $this
     * @throws ModelException
     * @throws ConfigLoadException
     */
    public function findAll(array $options = []): self
    {
        $this->select($options['columns'] ?? null)
            ->from()
            ->applyWhereOptions($options)
            ->groupBy($options['group_by'] ?? []);

        if (isset($options['order_by']))
        {
            $this->orderBy($options['order_by'][0], $options['order_by'][1]);
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
    private function applyWhereOptions(array $options): self
    {
        $this->queryBuilder->applyWhereOptions($options);

        return $this;
    }

    /**
     * Query builder: This function set the WHERE / AND condition.
     * Hint: If you want only to set a simple = condition, you can use only 2 parameters:
     * Example: ->where('column', 1)
     * SQL statement: WHERE column = 1 or AND column = 1
     * @param string $column
     * @param string|int|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @param bool $formatValue
     * @return Model
     */
    public function where(
        string $column,
        string|int|null $operator = null,
        string|int|float|null $value = null,
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
     * @param string $option
     * @return Model
     * @throws ModelException
     * @throws ConfigLoadException
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
     * @throws ConfigLoadException
     * @deprecated Use findFirst() instead
     */
    public function find_first(array $options = []): self
    {
        return $this->findFirst($options);
    }

    /**
     * @param array $options
     * @return Model
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     * @throws ConfigLoadException
     */
    public function findFirst(array $options = []): self
    {
        return $this->select($options['columns'] ?? null)
            ->from()
            ->applyWhereOptions($options)
            ->groupBy($options['group_by'] ?? [])
            ->orderBy($this->primaryKey())
            ->limit(1)
            ->execute()
            ->prepareFindResult();
    }

    /**
     * @return $this
     * @deprecated Use prepareFindResult() instead
     */
    public function prepare_find_result(): self
    {
        $this->prepareFindResult();

        return $this;
    }

    /**
     * @return $this
     */
    public function prepareFindResult(): self
    {
        $find_result = $this->getResult();

        if (false !== $find_result)
        {
            $this->result = $this->result[0];
            $this->setId($this->result[$this->primaryKey()]);
        }

        return $this;
    }

    /**
     * @return array|false
     * @deprecated Use getResult() instead
     */
    public function get_result(): array|false
    {
        return $this->getResult();
    }

    /**
     * @return array|false
     */
    public function getResult(): array|false
    {
        return $this->result;

    }

    /**
     * @param int|string $id
     * @return void
     */
    private function setId(int|string $id): void
    {
        $this->_id = $id;
    }

    /**
     * @return string
     * @deprecated Use primaryKey() instead
     */
    public function primary_key(): string
    {
        return $this->primaryKey();
    }

    /**
     * @return string
     */
    public function primaryKey(): string
    {
        return $this->primaryKey;
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
     * @throws ConfigLoadException
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
     * @throws ConfigLoadException
     */
    public function findLast(array $options = []): self
    {
        return $this->select($options['columns'] ?? null)
            ->from()
            ->applyWhereOptions($options)
            ->groupBy($options['group_by'] ?? [])
            ->orderBy($this->primaryKey(), 'DESC')
            ->limit(1)
            ->execute()
            ->prepareFindResult();
    }

    /**
     * @param string|int $id
     * @return Model
     * @throws ModelException
     * @throws ConfigLoadException
     * @deprecated Use findByPrimaryKey() instead
     */
    public function find_by_primary_key(string|int $id): self
    {
        return $this->findByPrimaryKey($id);
    }

    /**
     * @param string|int $id
     * @return Model
     * @throws ModelException
     * @throws ConfigLoadException
     */
    public function findByPrimaryKey(string|int $id): self
    {
        return $this->select()
            ->from()
            ->where($this->primaryKey(), $id)
            ->execute()
            ->prepareFindResult();
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

    /**
     * @param bool $value
     * @return $this
     */
    public function distinct(bool $value = true): self
    {
        $this->queryBuilder->distinct($value);

        return $this;
    }

    /**
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
     * @param string $column
     * @param string|null $operator
     * @param string|int|float|null $value
     * @param bool $backticks
     * @return Model
     * @deprecated Use orWhere() instead
     */
    public function or_where(string $column, ?string $operator = null, string|int|float|null $value = null, bool $backticks = true): self
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

    /**
     * @return $this
     * @deprecated Use andWhereClose() instead
     */
    public function and_where_close(): self
    {
        $this->whereClose();

        return $this;
    }

    /**
     * @return $this
     */
    public function andWhereClose(): self
    {
        $this->whereClose();

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
     * @return Model
     * @deprecated Use whereOpen() instead
     */
    public function where_open(): self
    {
        $this->queryBuilder->whereOpen();

        return $this;
    }

    /**
     * @return Model
     */
    public function whereOpen(): self
    {
        $this->queryBuilder->whereOpen();

        return $this;
    }

    /**
     * @return $this
     */
    public function and(): self
    {
        $this->queryBuilder->and();

        return $this;
    }

    /**
     * @return $this
     */
    public function or(): self
    {
        $this->queryBuilder->or();

        return $this;
    }

    /**
     * @return $this
     */
    public function open(): self
    {
        $this->queryBuilder->open();

        return $this;
    }

    /**
     * @return $this
     */
    public function close(): self
    {
        $this->queryBuilder->close();

        return $this;
    }

    /**
     * @param ?string $query
     * @return $this
     */
    public function query(string $query = null): self
    {
        $this->queryBuilder->query($query);

        return $this;
    }

    /**
     * @return array|false
     * @deprecated Use asArray() instead
     */
    public function as_array(): array|false
    {
        return $this->asArray();
    }

    /**
     * @return array|false
     */
    public function asArray(): array|false
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
     * @return false|object
     * @deprecated Use asObject() instead
     */
    public function as_object(): object|false
    {
        return $this->asObject();
    }

    /**
     * @return object|false
     */
    public function asObject(): object|false
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
     * @param string|int|float|null $value
     * @return Model
     * @throws ModelException
     * @throws ModelPrimaryKeyException
     * @throws ModelPropertyException
     */
    public function set(string|array $property, string|int|float|null $value = null): self
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
            $this->setData($property, $value);
        }

        return $this;
    }

    /**
     * This function set the data for the model properties.
     * @param string $property
     * @param string|int|float|null $value
     * @throws ModelPrimaryKeyException
     * @throws ModelPropertyException
     */
    private function setData(string $property, string|int|float|null $value): void
    {
        if ($property === $this->primaryKey())
        {
            throw new ModelPrimaryKeyException('Primary key on model ' . static::class . ' cannot be changed.');
        }

        if (!$this->propertyExists($property))
        {
            throw new ModelPropertyException(__CLASS__ . '::' . __FUNCTION__ . '(): self ' . static::class . ' has no property "' . $property . '"!');
        }
        $this->data[$property] = $value;
    }

    /**
     * @param string $property
     * @return bool
     * @deprecated Use propertyExists() instead
     */
    public function property_exists(string $property): bool
    {
        return $this->propertyExists($property);
    }

    /**
     * @param string $property
     * @return bool
     */
    public function propertyExists(string $property): bool
    {
        return array_key_exists($property, $this->properties());
    }


    /**
     * @param bool $keysOnly
     * @return array
     */
    public function properties(bool $keysOnly = false): array
    {
        foreach ($this->properties as $key => $p)
        {
            if (is_string($p))
            {
                unset($this->properties[$key]);
                $this->properties[$p] = [];
            }
        }

        if ($keysOnly)
        {
            return array_keys($this->properties);
        }

        return $this->properties;
    }

    /**
     * @return bool|int|string
     * @throws ModelException
     * @throws ModelPropertyException
     * @throws ConfigLoadException
     */
    public function save(): bool|int|string
    {
        if (!is_null($this->getId()))
        {
            return $this->update($this->getId(), $this->get_data());
        }

        return $this->insert($this->get_data());
    }

    /**
     * @return int|string
     */
    private function getId(): int|string
    {
        return $this->_id;
    }

    /**
     * @param int|string $id
     * @param array $data
     * @return bool
     * @throws ConfigLoadException
     * @throws ModelException
     * @throws ModelPropertyException
     */
    public function update(int|string $id, array $data = []): bool
    {
        if (empty($data))
        {
            return false;
        }

        $this->hasProperties($data);

        $sql = 'UPDATE ' . $this->table() . ' SET ' . $this->prepareUpdate($data) . ' WHERE ' . $this->formatter->backticks($this->primaryKey()) . ' = ' .
            $id;

        return Db::write($sql, $this->connection);
    }

    /**
     * @param array $data
     * @throws ModelPropertyException
     */
    private function hasProperties(array $data): void
    {
        foreach ($data as $key => $value)
        {
            if (!$this->propertyExists($key))
            {
                throw new ModelPropertyException(__CLASS__ . '::' . __FUNCTION__ . '(): self ' . static::class . ' has no property "' . $key . '"!');
            }
        }
    }

    /**
     * @param array $array
     * @return string|false
     * @throws ConfigLoadException
     * @deprecated Use prepareUpdate() instead
     */
    public function prepare_update(array $array): string|false
    {
       return $this->prepareUpdate($array);
    }

    /**
     * @param array $array
     * @return string|false
     * @throws ConfigLoadException
     */
    public function prepareUpdate(array $array): false|string
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
                $columns .= ' ' . $this->formatter->backticks(preg_replace('/[^a-z_A-Z0-9]/', '', $key)) . ' = NULL,';

                continue;
            }

            $columns .= ' ' . $this->formatter->backticks(preg_replace('/[^a-z_A-Z0-9]/', '', $key)) . ' = \'' . $this->escape($value) . '\',';
        }

        return rtrim($columns, ',');
    }

    /**
     * @param string|int|float|null $value
     * @return string
     * @throws ConfigLoadException
     */
    public function escape(string|int|float|null $value): string
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

        $this->hasProperties($data);
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
     * @param array $array
     * @return bool|array
     * @throws ConfigLoadException
     */
    public function prepare_insert(array $array): bool|array
    {
        if (empty($array))
        {
            return false;
        }

        $columns = $this->formatter->open();
        $columns_data = $this->formatter->open();

        foreach ($array as $key => $value)
        {
            $columns .= $this->formatter->backticks($key) . ',';

            if ($value === null)
            {
                $columns_data .= 'NULL,';

                continue;
            }

            $columns_data .= '"' . $this->escape($value) . '",';
        }

        $columns = rtrim($columns, ',');
        $columns .= $this->formatter->close();

        $columns_data = rtrim($columns_data, ',');
        $columns_data .= $this->formatter->close();

        return [$columns, $columns_data];
    }

    /**
     * @param string|int $id
     * @return bool
     * @throws ModelException
     * @throws ConfigLoadException
     */
    public function delete(string|int $id): bool
    {
        $sql = 'DELETE FROM ' . $this->table() . ' WHERE ' . $this->formatter->backticks($this->primaryKey()) . ' = ' . $id;

        return Db::write($sql, $this->connection);
    }

    /**
     * @param string $property
     * @return false|array
     * @deprecated Use dataValue() instead
     */
    public function data_value(string $property): false|array
    {
        return $this->dataValue($property);
    }

    /**
     * @param string $property
     * @return false|array
     */
    public function dataValue(string $property): false|array
    {
        $modelProperties = $this->properties();
        $dataType = $this->dataType($property);

        if (!$dataType || (!isset($modelProperties[$property]['length']) && !isset($modelProperties[$property]['set'])))
        {
            return false;
        }

        $dataValue = (isset($modelProperties[$property]['length'])) ? 'length' : 'set';

        return $modelProperties[$property][$dataValue];
    }


    /**
     * @param string $property
     * @return false|array
     * @deprecated Use dataType() instead
     */
    public function data_type(string $property): false|array
    {
        return $this->DataType($property);
    }

    /**
     * @param string $property
     * @return false|array
     */
    public function DataType(string $property): false|array
    {
        $modelProperties = $this->properties();

        if (!is_array($modelProperties[$property]) || !isset($modelProperties[$property]['data_type']))
        {
            return false;
        }

        return $modelProperties[$property]['data_type'];
    }

    /**
     * @return array
     * @throws ConfigLoadException
     * @throws ModelException
     * @deprecated Use fieldNames() instead
     */
    public function field_names(): array
    {
        return $this->fieldNames();
    }

    /**
     * @return array
     * @throws ConfigLoadException
     * @throws ModelException
     */
    public function fieldNames(): array
    {
        $dataFromBb = Db::field_names($this->compile(), $this->connection);

        if (!empty($dataFromBb))
        {
            $this->setDbSchema($dataFromBb);
        }

        return $this->getDbSchema();
    }

    /**
     * @param array $schema
     * @return void
     */
    private function setDbSchema(array $schema): void
    {
        $this->dbSchema = $schema;
    }

    /**
     * @return array
     */
    private function getDbSchema(): array
    {
        return $this->dbSchema;
    }

    /**
     * @return bool
     * @deprecated Use hasResult() instead
     */
    public function has_result(): bool
    {
        return $this->hasResult();
    }

    /**
     * @return bool
     */
    public function hasResult(): bool
    {
        return false !== $this->result;
    }

    /**
     * @return $this
     * @throws ConfigLoadException
     * @throws ModelException
     * @deprecated Use getCount() instead
     */
    public function get_count() :self
    {
        return $this->getCount();
    }

    /**
     * @return $this
     * @throws ConfigLoadException
     * @throws ModelException
     */
    public function getCount(): self
    {
        $dataFromDb = Db::read($this->getCountCompile(), $this->connection);

        if ($dataFromDb !== false)
        {
            $this->result = $dataFromDb;
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
     * @deprecated Use getCountCompile() instead
     */
    public function get_count_compile(): ?string
    {
        return $this->getCountCompile();
    }

    /**
     * @return string|null
     * @throws ModelException
     */
    public function getCountCompile(): ?string
    {
        return $this->queryBuilder->getCountCompile();
    }

    /**
     * @return string|null
     * @deprecated Use getTableAlias() instead
     */
    public function get_table_alias(): ?string
    {
        return $this->tableAlias;
    }

    /**
     * @return string|null
     */
    public function getTableAlias(): ?string
    {
        return $this->tableAlias;
    }

    /**
     * @param string $alias
     * @return $this
     * @throws ModelException
     * @deprecated Use setTableAlias() instead
     */
    public function set_table_alias(string $alias): self
    {
        $this->setTableAlias($alias);

        return $this;
    }

    /**
     * @param string $alias
     * @return $this
     * @throws ModelException
     */
    public function setTableAlias(string $alias): self
    {
        $this->tableAlias = $alias;

        $this->rebuildQueryBuilder();

        return $this;
    }

    /**
     * @param string $columnName
     * @return array
     * @throws ModelException
     * @deprecated Use getDefault() instead
     */
    public function get_default(string $columnName): array
    {
        return $this->getDefault($columnName);
    }

    /**
     * @param string $columnName
     * @return array
     * @throws ModelException
     */
    public function getDefault(string $columnName): array
    {
        if (!$this->propertyExists($columnName))
        {
            throw new ModelException(sprintf('column name does not exist on Model: %s::%s', __CLASS__, $columnName));
        }

        if (!isset($this->properties[$columnName]['default']))
        {
            throw new ModelException(sprintf('default definition on %s does not exist', $columnName));
        }

        return $this->properties[$columnName]['default'];
    }
}
