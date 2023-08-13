<?php

declare(strict_types=1);

namespace Asterios\Core\Db\Connection;

use Asterios\Core\Db\Exceptions\DbQueryException;
use Asterios\Core\Db\ORM\Statement;
use PDO;
use Asterios\Core\Interfaces\Db\ConnectionInterface;
use Asterios\Core\Exception\DbConnectionManagerException;
use Asterios\Core\Db\ORM\Support\Collections\ResultCollection;
use PDOException;

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
        catch (PDOException $e)
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
    /**
     * @inheritdoc
     */
    public function exec(string $statement): false|int
    {
        return $this->connection->exec($statement);
    }
    /**
     * @inheritdoc
     */
    public function errorCode(): ?string
    {
        return $this->connection->errorCode();
    }

    /**
     * @inheritdoc
     */
    public function errorInfo(): array
    {
        return $this->connection->errorInfo();
    }

    /**
     * @inheritdoc
     */
    public function errorDriverCode(): int
    {
        $error = $this->errorInfo();
        if (is_array($error) && isset($error[2]))
        {
            return (int) $error[1];
        }

        return 0;
    }

    /**
     * @inheritdoc
     */
    public function errorMessage(): string
    {
        $error = $this->errorInfo();
        if (is_array($error) && isset($error[2]))
        {
            return (string) $error[2];
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function query(string $query): Statement|false
    {
        try
        {
            return new Statement($this->connection->query($query, PDO::FETCH_OBJ));
        }
        catch (PDOException $e)
        {
            throw new DbQueryException(
                message: $e->getMessage(),
                code: $e->getCode(),
                previous: $e->getPrevious()
            );
        }
    }
}