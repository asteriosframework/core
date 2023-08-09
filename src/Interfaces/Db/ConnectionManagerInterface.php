<?php

declare(strict_types=1);

namespace Asterios\Core\Interfaces\Db;

use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\DbConnectionManagerException;

interface ConnectionManagerInterface
{
    /**
     * @param string $config_group
     * @return ConnectionManagerInterface
     * @throws ConfigLoadException|DbConnectionManagerException
     */
    public static function create(string $config_group = 'connections.default'): ConnectionManagerInterface;

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface;
}