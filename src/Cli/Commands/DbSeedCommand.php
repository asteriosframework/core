<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Interfaces\CommandInterface;

#[Command(
    name: 'db:seed',
    description: 'Run database seeding',
    group: 'Database',
    aliases: ['--ds']
)]
class DbSeedCommand implements CommandInterface
{
    use CommandsBuilderTrait;

    public function handle(?string $argument): void
    {
        // TODO: Implement handle() method.
    }
}