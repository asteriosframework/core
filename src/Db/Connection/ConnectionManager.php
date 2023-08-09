<?php

declare(strict_types=1);

namespace Asterios\Core\Db\Connection;

use Asterios\Core\Config;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\DbConnectionManagerException;
use Asterios\Core\Interfaces\Db\ConnectionInterface;
use Asterios\Core\Interfaces\Db\ConnectionManagerInterface;
use PDO;

class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * @var object
     */
    protected object $config;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @param string $config_group
     * @throws ConfigLoadException|DbConnectionManagerException
     */
    public function __construct(string $config_group = 'connections.default')
    {
        $this->config = Config::get('db', $config_group);

        $this->connection = match ($this->config->type)
        {
            'mysql' => $this->mysqlConnect(),
            default => throw new DbConnectionManagerException(message: 'Driver "' . $this->config->type . '" not found'),
        };
    }

    /**
     * @inheritdoc
     */
    public static function create(string $config_group = 'connections.default'): ConnectionManagerInterface
    {
        return new self($config_group);
    }

    /**
     * @inheritdoc
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @return ConnectionInterface
     * @throws DbConnectionManagerException
     */
    protected function mysqlConnect(): ConnectionInterface
    {
        $this->config->{'$dsn'} = "mysql:host={$this->config->db_host};dbname={$this->config->db_database};charset={$this->config->db_charset}";

        return MysqlConnection::connect(
            dsn: $this->config->dsn,
            username: $this->config->db_user,
            password: $this->config->db_password,
            options: $this->config->attributes
        );
    }
}