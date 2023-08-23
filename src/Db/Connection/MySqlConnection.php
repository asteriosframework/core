<?php

declare(strict_types=1);

namespace Asterios\Core\Db\Connection;

use Asterios\Core\Db\Exceptions\DbException;
use Asterios\Core\Db\Exceptions\DbQueryException;
use Asterios\Core\Db\ORM\Statement;
use PDO;
use Asterios\Core\Interfaces\Db\ConnectionInterface;
use Asterios\Core\Exception\DbConnectionManagerException;
use Asterios\Core\Db\ORM\Support\Collections\ResultCollection;
use PDOException;

class MySqlConnection implements ConnectionInterface
{
    protected ?PDO $connection = null;

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

    public function __destruct()
    {
        $this->disconnect();
    }

    public function disconnect(): void
    {
        $this->connection = null;
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
        try
        {
            return $this->connection->exec($statement);
        }
        catch (PDOException $e)
        {
            throw new DbException(
                message: $e->getMessage(),
                code: (int) $e->getCode(),
                previous: $e->getPrevious()
            );
        }
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
        return (int) $error[1];
    }

    /**
     * @inheritdoc
     */
    public function errorMessage(): string
    {
        $error = $this->errorInfo();
        return (string) $error[2];
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
                code: (int) $e->getCode(),
                previous: $e->getPrevious()
            );
        }
    }
}