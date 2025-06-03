<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Db\Seeder;
use Asterios\Core\Enum\CliStatusIcon;

#[Command(
    name: 'db:seed',
    description: 'Run database seeding',
    group: 'Database',
    aliases: ['--ds']
)]
class DbSeedCommand extends BaseCommand
{
    use CommandsBuilderTrait;

    /** @codeCoverageIgnoreSart */
    public function handle(?string $argument): void
    {
        $this->handleWithSeeder(new Seeder());
    }

    // @codeCoverageIgnoreEnd

    public function handleWithSeeder(Seeder $seeder): void
    {
        $this->printHeader();

        $seeder->seed();
        $messages = $seeder->getMessages();

        foreach ($messages as $seederMessage)
        {
            foreach ($seederMessage as $filename => $status)
            {
                $status = match ($status)
                {
                    'done' => CliStatusIcon::Success->icon() . 'Seeded',
                    'failed' => CliStatusIcon::Error->icon() . 'Seeding failed',
                    default => CliStatusIcon::Unknown->icon() . 'Seeding in unknown state',
                };

                echo $status . ' ' . $filename . PHP_EOL;
            }
        }
    }
}
