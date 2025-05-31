<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

use Closure;

interface SchemaInterface
{
    /**
     * @param string $table
     * @param Closure $callback
     * @param string $engine
     * @param string $charSet
     * @return void
     */
    public static function create(string $table, Closure $callback, string $engine = 'InnoDB', string $charSet = 'utf8mb4'): void;

    /**
     * @param string $table
     * @return void
     */
    public static function drop(string $table): void;
}
