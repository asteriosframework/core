<?php

declare(strict_types=1);

namespace Asterios\Core\Interfaces\Db;

use Asterios\Core\Db\ORM\Statement;
use Asterios\Core\Db\Exceptions\DbException;
use Asterios\Core\Db\Exceptions\DbQueryException;
use Asterios\Core\Exception\DbConnectionManagerException;

interface ConnectionInterface
{
    /**
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array|null $options
     * @return ConnectionInterface
     * @throws DbConnectionManagerException
     */
    public static function connect(
        string $dsn,
        string|null $username = null,
        string|null $password = null,
        array|null $options = null
    ): ConnectionInterface;

    /**
     * @return null|string
     */
    public function errorCode(): ?string;

    /**
     * @return array
     */
    public function errorInfo(): array;

    /**
     * @return int
     */
    public function errorDriverCode(): int;

    /**
     * @return string
     */
    public function errorMessage(): string;

    /**
     * @param string $statement
     * @return int|false
     * @throws DbException
     */
    public function exec(string $statement): int|false;

    /**
     * @param string $query
     * @return Statement|false
     * @throws DbQueryException
     */
    public function query(string $query): Statement|false;
}