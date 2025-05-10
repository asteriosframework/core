<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

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
     * @return string[]
     */
    public function getErrors(): array;

    /**
     * @return string|null
     */
    public function getMigrationsPath(): ?string;

    /**
     * @return string[]
     */
    public function getMessages(): array;

    /**
     * @return self
     */
    public function force(): self;
}