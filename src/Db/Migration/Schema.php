<?php declare(strict_types=1);

namespace Asterios\Core\Db\Migration;

use Asterios\Core\Contracts\SchemaInterface;
use Asterios\Core\Db;
use Asterios\Core\Db\Builder\SchemaBuilder;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Logger;
use Closure;

class Schema implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(string $table, Closure $callback, string $engine = 'InnoDB', string $charSet = 'utf8mb4'): void
    {
        $schemaBuilder = new SchemaBuilder($table);
        $callback($schemaBuilder);

        [$columns, $foreignKeys, $indexes] = $schemaBuilder->build();

        $definitionParts = array_filter(array_merge($columns, $foreignKeys, $indexes));

        $indented = array_map(static fn($line) => '    ' . $line, $definitionParts);
        $sqlStatements = implode(",\n", $indented);

        $sql = "CREATE TABLE `$table` (\n" . $sqlStatements . "\n) ENGINE=$engine DEFAULT CHARSET=$charSet;";

        try
        {
            Db::write($sql);
        } catch (ConfigLoadException $e)
        {
            Logger::forge()
                ->fatal('Create migration for table ' . $table . ' failed: ' . $e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public static function drop(string $table): void
    {
        $sql = 'DROP TABLE IF EXISTS `' . $table . '`';

        try
        {
            Db::write($sql);
        } catch (ConfigLoadException $e)
        {
            Logger::forge()
                ->fatal('Drop migration for table ' . $table . ' failed: ' . $e->getMessage());
        }
    }
}
