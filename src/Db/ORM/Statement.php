<?php

declare(strict_types=1);

namespace Asterios\Core\Db\ORM;

use PDO;
use PDOStatement;

final class Statement
{
    public const FETCH_DEFAULT = PDO::FETCH_DEFAULT;
    public const FETCH_COLUMN = PDO::FETCH_COLUMN;
    public const FETCH_CLASS = PDO::FETCH_CLASS;
    public const FETCH_FUNC = PDO::FETCH_FUNC;

    protected PDOStatement $statement;

    /**
     * @param \PDOStatement $statement
     */
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * @param int $mode
     * @param null|callable|string $callbackClass
     * @param null|array $constructorArgs
     * @return array
     */
    public function fetchAll(int $mode, null|callable |string $callbackClass = null, null|array $constructorArgs = null): array
    {
        return $this->statement->fetchAll($mode, $callbackClass, $constructorArgs);
    }
}