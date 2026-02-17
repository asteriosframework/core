<?php

declare(strict_types=1);

namespace Asterios\Core;

class Db
{
    /** @var \mysqli|null */
    private $connection;
    /** @var Db[] */
    private static $instance = [];
    /** @var string */
    private $host;
    /** @var string */
    private $username;
    /** @var string */
    private $password;
    /** @var string */
    private $database;
    /** @var string */
    private $charset;
    /** @var  bool|\mysqli_result */
    private $result;

    /**
     * @throws Exception\ConfigLoadException
     */
    public static function forge(string $config_group = 'default'): Db
    {
        if (!isset(self::$instance[$config_group]))
        {
            self::$instance[$config_group] = new self($config_group);
        }

        return self::$instance[$config_group];
    }

    /**
     * @throws Exception\ConfigLoadException
     */
    private function __construct(string $config_group)
    {
        $config = Config::get('db', $config_group);

        $this->set_host($config->host);
        $this->set_username($config->username);
        $this->set_password($config->password);
        $this->set_database($config->database);
        $this->set_charset($config->charset);

        $this->connection = new \mysqli($this->get_host(), $this->get_username(), $this->get_password(), $this->get_database());

        // Error handling
        if (\mysqli_connect_error())
        {
            \trigger_error("Failed to connect to MySQL: " . \mysqli_connect_error(), E_USER_ERROR);
        }

        $this->connection->set_charset($this->get_charset());
    }

    public function __destruct()
    {
        $this->get_connection()
            ->close();
    }

    public function get_connection(): \mysqli
    {
        return $this->connection;
    }

    /**
     * @param string $sql
     * @param string $config_group
     * @return array|bool
     * @throws Exception\ConfigLoadException
     */
    public static function read(string $sql, string $config_group = 'default')
    {
        $instance = self::forge($config_group);

        $instance->result = $instance->get_connection()
            ->query($sql, MYSQLI_USE_RESULT);

        $result_array = [];

        if ($instance->result !== false)
        {
            while ($row = $instance->result->fetch_assoc())
            {
                $result_array[] = $row;
            }

            $instance->result->free_result();
        }

        if (!empty($result_array))
        {
            return $result_array;
        }

        return false;
    }

    /**
     * @param string $sql
     * @param string $config_group
     * @return false|int|string
     * @throws Exception\ConfigLoadException
     */
    public static function insert(string $sql, string $config_group = 'default')
    {
        $response = false;
        $connection = self::forge($config_group)
            ->get_connection();

        if (true === $connection->query($sql))
        {
            $response = $connection->insert_id;
        }

        return $response;
    }

    /**
     * @throws Exception\ConfigLoadException
     */
    public static function escape(string $value, string $config_group = 'default'): string
    {
        return self::forge($config_group)
            ->get_connection()
            ->real_escape_string($value);
    }

    /**
     * Quote a value for use in an SQL statement.
     *
     * @param string|int|float|null|bool $value
     * @param string $config_group
     * @return string
     * @throws Exception\ConfigLoadException
     */
    public static function quote(string|int|float|null|bool $value, string $config_group = 'default'): string
    {
        $connection = self::forge($config_group)
            ->get_connection();

        if (is_null($value))
        {
            return 'NULL';
        }

        if (is_bool($value))
        {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value))
        {
            return (string)$value;
        }

        return "'" . $connection->real_escape_string($value) . "'";
    }

    /**
     * @throws Exception\ConfigLoadException
     */
    public static function write(string $sql, string $config_group = 'default'): bool
    {
        $write = true;

        if (
            true !== self::forge($config_group)
            ->get_connection()
            ->query($sql)
        ) {
            $write = false;
        }

        return $write;
    }

    /**
     * Prepare sql update set values.
     *
     * @param array $array
     * @return bool|string
     */
    public static function prepare_update(array $array)
    {
        if (empty($array))
        {
            return false;
        }

        $columns = '';

        foreach ($array as $key => $value)
        {
            $columns .= ' `' . preg_replace('/[^a-z_A-Z0-9]/', '', $key) . '` = \'' . $value . '\',';
        }

        return rtrim($columns, ',');
    }

    /**
     * @throws Exception\ConfigLoadException
     */
    public static function server_info(string $config_group = 'default'): string
    {
        return self::forge($config_group)
            ->get_connection()->server_info;
    }

    /**
     * @throws Exception\ConfigLoadException
     */
    public static function field_names(string $sql, string $config_group = 'default'): array
    {
        $return_data = [];
        $query = self::forge($config_group)
            ->get_connection()
            ->query($sql);

        if (false !== $query)
        {

            $fields = $query->fetch_fields();

            foreach ($fields as $field)
            {
                $return_data[] = $field->name;
            }
        }

        return $return_data;
    }

    /**
     * @param string $table
     * @param array $foreignKeys
     * @param bool $dropTable
     * @param null|string $migrationPath
     * @return bool
     * @throws Exception\ConfigLoadException
     */
    public function migrate(string $table, array $foreignKeys = [], bool $dropTable = false, null|string $migrationPath = null): bool
    {
        if (null === $migrationPath)
        {
            return false;
        }

        $tableCheckQuery = self::forge()
            ->get_connection()
            ->query('SHOW TABLES LIKE "' . $table . '"');
        $hasTable = ($tableCheckQuery instanceof \mysqli_result) && $tableCheckQuery->num_rows > 0;

        if ($dropTable)
        {
            if ($hasTable)
            {
                foreach ($foreignKeys as $referenceTable => $values)
                {
                    foreach ($values as $foreignKey)
                    {
                        self::forge()
                            ->get_connection()
                            ->query('ALTER TABLE ' . $referenceTable . ' DROP FOREIGN KEY IF EXISTS ' . $foreignKey);
                    }
                }
            }

            self::forge()
                ->get_connection()
                ->query('DROP TABLE IF EXISTS ' . $table);
        }

        $sqlFile = $migrationPath . $table . '.sql';

        $sqlScript = file_get_contents($sqlFile);

        self::forge()
            ->get_connection()
            ->multi_query($sqlScript);

        $count = 0;

        while (self::forge()
            ->get_connection()
            ->next_result()
        ) {
            ++$count;
        }

        return $count !== 0;
    }

    /**
     * @param string $seederFile
     * @return void
     * @throws Exception\ConfigLoadException
     * @throws \JsonException
     */
    public static function seedFromFile(string $seederFile): void
    {
        if (!file_exists($seederFile))
        {
            throw new \RuntimeException("Seed file not found: $seederFile");
        }

        $json = file_get_contents($seederFile);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data) || empty($data))
        {
            throw new \RuntimeException("Invalid or empty JSON in seed file: $seederFile");
        }

        $table = basename($seederFile, '.json');

        foreach ($data as $row)
        {
            if (!is_array($row))
            {
                continue;
            }

            $columns = array_map(static fn ($col) => '`' . $col . '`', array_keys($row));
            $values = array_map([self::class, 'quote'], array_values($row));

            $sql = 'INSERT INTO `' . $table . '` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ');';

            self::write($sql);
        }
    }

    /**
     * @return bool
     * @throws Exception\ConfigLoadException
     */
    public static function isMariaDb(): bool
    {
        $info = mysqli_get_server_info(self::forge()
            ->get_connection());

        return stripos($info, 'mariadb') !== false;
    }

    private function set_host(string $host): void
    {
        $this->host = $host;
    }

    private function set_username(string $username): void
    {
        $this->username = $username;
    }

    private function set_password(string $password): void
    {
        $this->password = $password;
    }

    private function set_database(string $database): void
    {
        $this->database = $database;
    }

    private function set_charset(string $charset): void
    {
        $this->charset = $charset;
    }

    private function get_host(): string
    {
        return $this->host;
    }

    private function get_username(): string
    {
        return $this->username;
    }

    private function get_password(): string
    {
        return $this->password;
    }

    private function get_database(): string
    {
        return $this->database;
    }

    private function get_charset(): string
    {
        return $this->charset;
    }
}
