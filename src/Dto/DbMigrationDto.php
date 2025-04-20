<?php declare(strict_types=1);

namespace Asterios\Core\Dto;

class DbMigrationDto
{

    /**
     * @var string[]
     */
    protected array $seeder = [];

    protected bool $dropTables = true;

    protected bool $truncateTables = true;

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