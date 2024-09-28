<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces;

interface InstallerInterface
{
    public function isInstalled(): bool;

    public function setIsInstalled(): bool;

    public function createMediaFolders(): self;

    public function setRunDatabaseSeeder(bool $value): self;

    public function setRunDatabaseMigrations(bool $value): self;
}