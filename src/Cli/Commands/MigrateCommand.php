<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Db\Migration;
use Asterios\Core\Enum\CliStatusIcon;

#[Command(
    name: 'migrate',
    description: 'Run all outstanding migrations',
    group: 'Database',
    aliases: ['--m'],
    options: [
        '--force' => 'Re-run already executed migrations',
    ]
)]
class MigrateCommand extends BaseCommand
{
    public function handle(?string $argument): void
    {
        $this->printHeader();

        if ($this->hasFlag('--help'))
        {
            $this->printCommandHelpFromAttribute();

            return;
        }

        $migration = $this->createMigration();

        if ($this->hasFlag('--force'))
        {
            $migration->force();
        }

        $migration->migrate();

        /** @var array $messages */
        $messages = $migration->getMessages();

        foreach ($messages as $migrationMessage)
        {
            foreach ($migrationMessage as $filename => $status)
            {
                $status = match ($status)
                {
                    'done' => CliStatusIcon::Success->icon() . 'Migrated',
                    'skipped' => CliStatusIcon::Warning->icon() . 'Skipped migration',
                    'missing' => CliStatusIcon::Danger->icon() . 'Missing method "up" in migration',
                    'failed' => CliStatusIcon::Error->icon() . 'Migration failed',
                    default => CliStatusIcon::Unknown->icon() . 'Migration in unknown state',
                };

                echo $status . ' ' . $filename . PHP_EOL;
            }
        }
    }

    /** @codeCoverageIgnoreSart */
    protected function createMigration(): Migration
    {
        return new Migration();
    }
    /** @codeCoverageIgnoreEnd */
}
