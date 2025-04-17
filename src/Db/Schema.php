<?php declare(strict_types=1);

namespace Asterios\Core\Db;

use Asterios\Core\Db;
use Closure;

class Schema
{
    public static function create(string $table, Closure $callback): void
    {
        $blueprint = new TableBluePrint($table);
        $callback($blueprint);

        $sql = $blueprint->toSql();

        Db::write($sql);
    }

    public static function drop(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS `$table`";
        Db::write($sql);
    }
}
