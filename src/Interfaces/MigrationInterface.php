<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces;

use PHPUnit\TextUI\XmlConfiguration\MigrationException;

interface MigrationInterface
{
    /**
     * @return bool
     * @throws MigrationException
     */
    public function migrate(): bool;

    /**
     * @param bool $truncateTables
     * @return bool
     */
    public function seed(bool $truncateTables = true): bool;

    /**
     * @return bool
     * @throws MigrationException
     */
    public function rollback(): bool;

    public function getErrors(): array;

}