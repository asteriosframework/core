<?php

declare(strict_types=1);

namespace Asterios\Core\Db\Connection;

use Asterios\Core\Exception\DbConnectionManagerException;
use Asterios\Core\Interfaces\Db\ConnectionInterface;
use PDO;

class MysqlConnection implements ConnectionInterface
{
    protected PDO $connection;

    /**
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array|null $options
     * @throws DbConnectionManagerException
     */
    public function __construct(
        protected string $dsn,
        protected string|null $username = null,
        protected string|null $password = null,
        protected array|null $options = null
    ) {
        try
        {
            $this->connection = new PDO(
                dsn: $this->dsn,
                username: $this->username,
                password: $this->password,
                options: $this->options
            );
        }
        catch (\PDOException $e)
        {
            throw new DbConnectionManagerException(
                message: $e->getMessage(),
                code: $e->getCode(),
                previous: $e
            );
        }
    }


    /**
     * @inheritdoc
     */
    public static function connect(
        string $dsn,
        string|null $username = null,
        string|null $password = null,
        array|null $options = null
    ): ConnectionInterface {
        return new self(
            dsn: $dsn,
            username: $username,
            password: $password,
            options: $options
        );
    }
}