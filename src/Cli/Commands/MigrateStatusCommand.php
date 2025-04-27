<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Db\Migration;
use Asterios\Core\Enum\CliStatusIcon;
use Asterios\Core\Interfaces\CommandInterface;

#[Command(
    name: 'migrate:status',
    description: 'Show the status of all migrations (ran/pending)',
    group: 'Database',
    aliases: ['--mi']
)]
class MigrateStatusCommand implements CommandInterface
{
    use CommandsBuilderTrait;

    public function handle(?string $argument): void
    {
        $this->printHeader();

        $migration = new Migration();
        $ranMigrations = $migration->getRanMigrations();
        $allMigrationFiles = $migration->getAllMigrationFiles();

        $statusList = [];

        foreach ($allMigrationFiles as $migrationFile)
        {
            $migrationName = pathinfo($migrationFile, PATHINFO_FILENAME);
            $isRan = $migration->hasMigrated($ranMigrations, $migrationName);

            $statusList[] = [
                'Status' => $isRan ? CliStatusIcon::Success->icon() . 'Migrated' : CliStatusIcon::Pending->icon() . 'Pending',
                'Migration' => $migrationName,
            ];
        }

        if (empty($statusList))
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
