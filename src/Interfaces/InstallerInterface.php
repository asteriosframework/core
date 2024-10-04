<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces;

use Asterios\Core\Enum\MediaEnum;

interface InstallerInterface
{
    public function isInstalled(): bool;

    public function setIsInstalled(): bool;

    public function createMediaFolders(): self;

    public function setRunDatabaseSeeder(bool $value): self;

    public function setRunDatabaseMigrations(bool $value): self;

    public function runDbMigrations(): self;

    public function runDbSeeders(): self;

    public function run(bool $createMediaFolders = false, bool $runDbMigration = false, bool $runDbSeeder = false): bool;

    public function createMediaFolder(string $mediaFolder, MediaEnum $type): bool;
}