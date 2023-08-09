<?php

declare(strict_types=1);

namespace Asterios\Core\Interfaces\Db;

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
}