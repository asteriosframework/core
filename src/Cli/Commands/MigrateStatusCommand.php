<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Db\Migration;
use Asterios\Core\Enum\CliStatusIcon;

#[Command(
    name: 'migrate:status',
    description: 'Show the status of all migrations (ran/pending)',
    group: 'Database',
    aliases: ['--mi']
)]
class MigrateStatusCommand extends BaseCommand
{
    private Migration $migration;

    public function __construct(?Migration $migration = null)
    {
        parent::__construct();
        $this->migration = $migration ?? new Migration();
    }

    public function handle(?string $argument): void
    {
        $this->printHeader();

        $ranMigrations = $this->migration->getRanMigrations();
        $allMigrationFiles = $this->migration->getAllMigrationFiles();

        $statusList = [];

        foreach ($allMigrationFiles as $migrationFile)
        {
            $migrationName = pathinfo($migrationFile, PATHINFO_FILENAME);
            $isRan = $this->migration->hasMigrated($ranMigrations, $migrationName);

            $statusList[] = [
                'Status' => $isRan
                    ? CliStatusIcon::Success->icon() . 'Migrated'
                    : CliStatusIcon::Pending->icon() . 'Pending',
                'Migration' => $migrationName,
            ];
        }

        if ([] === $statusList)
        {
            echo CliStatusIcon::Warning->icon() . 'No migrations were found.' . PHP_EOL;
        }
        else
        {
            $this->printListTable(
                'Database Migration Status',
                $statusList,
                'Status',
                'Migration',
            );
        }
    }
}
