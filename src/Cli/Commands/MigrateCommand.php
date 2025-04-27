<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Db\Migration;
use Asterios\Core\Enum\CliStatusIcon;
use Asterios\Core\Interfaces\CommandInterface;

#[Command(
    name: 'migrate',
    description: 'Run all outstanding migrations',
    group: 'Database'
)]
class MigrateCommand implements CommandInterface
{
    use CommandsBuilderTrait;

    public function handle(?string $argument): void
    {
        $this->printHeader();

        $migration = new Migration();
        $migration->migrate();

        $messages = $migration->getMessages();

        foreach ($messages as $message)
        {

            $status = match ($message['status'])
            {
                'done' => CliStatusIcon::Success->icon() . 'Migrated',
                'skipped' => CliStatusIcon::Warning->icon() . 'Skipped migration',
                'missing' => CliStatusIcon::Danger->icon() . 'Missing method "up" in migration',
                'failed' => CliStatusIcon::Error->icon() . 'Migration failed',
                default => CliStatusIcon::Unknown->icon() . 'Migration in unknown state',
            };

            echo $status . ' ' . $message['name'] . "\n";
        }

    }
}
