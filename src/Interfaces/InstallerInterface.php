<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces;

use Asterios\Core\Enum\MediaModeEnum;

interface InstallerInterface
{
    /**
     * @return bool
     */
    public function isInstalled(): bool;

    /**
     * @return bool
     */
    public function setIsInstalled(): bool;

    /**
     * @return string
     */
    public function getInstalledFile(): string;

    /**
     * @return self
     */
    public function createMediaFolders(): self;

    /**
     * @param bool $value
     * @return self
     */
    public function setRunDatabaseSeeder(bool $value): self;

    /**
     * @param bool $value
     * @return self
     */
    public function setRunDatabaseMigrations(bool $value): self;

    /**
     * @return self
     */
    public function runDbMigrations(): self;

    /**
     * @param bool $truncateTables
     * @return self
     */
    public function runDbSeeders(bool $truncateTables = true): self;

    /**
     * @param bool $createMediaFolders
     * @param bool $runDbMigration
     * @param bool $runDbSeeder
     * @param bool $truncateTables
     * @return bool
     */
    public function run(bool $createMediaFolders = false, bool $runDbMigration = false, bool $runDbSeeder = false, bool $truncateTables = true): bool;

    /**
     * @param string $mediaFolder
     * @param MediaModeEnum $type
     * @return bool
     */
    public function createMediaFolder(string $mediaFolder, MediaModeEnum $type): bool;
}