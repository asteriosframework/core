<?php declare(strict_types=1);

namespace Asterios\Core\Dto;

class DbMigrationDto
{
    /** @var string[] */
    protected array $tablesToMigrate = [];

    /** @var array<string, array<int, string>> */
    protected array $foreignKeysToMigrate = [];

    /**
     * @var string[]
     */
    protected array $seeder = [];

    protected bool $dropTables = true;

    protected bool $truncateTables = true;

    public function setTablesToMigrate(array $tablesToMigrate): DbMigrationDto
    {
        $this->tablesToMigrate = $tablesToMigrate;

        return $this;
    }

    public function getTablesToMigrate(): array
    {
        return $this->tablesToMigrate;
    }

    public function setForeignKeysToMigrate(array $foreignKeysToMigrate): DbMigrationDto
    {
        $this->foreignKeysToMigrate = $foreignKeysToMigrate;

        return $this;
    }

    public function getForeignKeysToMigrate(): array
    {
        return $this->foreignKeysToMigrate;
    }

    public function setSeeder(array $seeder): DbMigrationDto
    {
        $this->seeder = $seeder;

        return $this;
    }

    public function getSeeder(): array
    {
        return $this->seeder;
    }

    public function setDropTables(bool $dropTables): DbMigrationDto
    {
        $this->dropTables = $dropTables;

        return $this;
    }

    public function dropTables(): bool
    {
        return $this->dropTables;
    }

    public function setTruncateTables(bool $truncateTables): DbMigrationDto
    {
        $this->truncateTables = $truncateTables;

        return $this;
    }

    public function truncateTables(): bool
    {
        return $this->truncateTables;
    }
}