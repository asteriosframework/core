<?php

declare(strict_types=1);

namespace Asterios\Core\Db\ORM;

use PDOException;
use Asterios\Core\Str;
use Asterios\Core\Config;
use Asterios\Core\Db\Exceptions\DbException;
use Asterios\Core\Db\Exceptions\DbQueryException;
use Asterios\Core\Interfaces\Db\ConnectionInterface;
use Asterios\Core\Interfaces\Db\ConnectionManagerInterface;
use Asterios\Core\Db\ORM\Support\Collections\ResultCollection;

class BaseModel
{
    /**
     * @param string $query
     * @param string $class
     * @param ConnectionManagerInterface|null $connectionManager
     * @throws DbQueryException
     * @return ResultCollection|false
     */
    public static function read(string $query, string $class = null, ConnectionManagerInterface $connectionManager = null): ResultCollection|false
    {
        $conn = self::connect($connectionManager);


        $statement = $conn->query($query);

        if (null !== $class)
        {
            /** @var array<int, mixed> $result */
            $result = $statement->fetchAll(Statement::FETCH_CLASS, $class);
        }
        else
        {
            $result = $statement->fetchAll(Statement::FETCH_DEFAULT);
        }

        if ([] === $result)
        {
            return false;
        }

        return new ResultCollection($result);
    }

    /**
     * @param ConnectionManagerInterface|null $connectionManager
     * @return ConnectionInterface
     */
    private static function connect(ConnectionManagerInterface $connectionManager = null): ConnectionInterface
    {
        if (null !== $connectionManager)
        {
            $conn = $connectionManager->getConnection();
        }
        else
        {
            /** @var ConnectionInterface $conn */
            $conn = Config::get_memory('DbConnection');
        }

        return $conn;
    }
}