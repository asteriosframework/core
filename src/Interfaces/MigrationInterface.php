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
     * @return bool
     * @throws MigrationException
     */
    public function rollback(): bool;

    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @return string|null
     */
    public function getMigrationsPath(): ?string;
}