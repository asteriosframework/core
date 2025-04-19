<?php declare(strict_types=1);

namespace Asterios\Core\Db\Migration;

use Asterios\Core\Db;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\MigrationException;
use Closure;

class Schema
{
    /**
     * @param string $table
     * @param Closure $callback
     * @return void
     * @throws MigrationException
     */
    public static function create(string $table, Closure $callback): void
    {
        $blueprint = new TableBluePrint($table);
        $callback($blueprint);

        $sql = $blueprint->toSql();

        try
        {
            Db::write($sql);
        } catch (ConfigLoadException $e)
        {
            throw new MigrationException('Create migration for table ' . $table . ' failed: ' . $e->getMessage());
        }
    }

    /**
     * @param string $table
     * @return void
     * @throws MigrationException
     */
    public static function drop(string $table): void
    {
        $sql = 'DROP TABLE IF EXISTS `' . $table . '`';

        try
        {
            Db::write($sql);
        } catch (ConfigLoadException $e)
        {
            throw new MigrationException('Drop migration for table ' . $table . ' failed: ' . $e->getMessage());
        }
    }
}
