<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Interfaces\CommandInterface;

#[Command(
    name: 'list',
    description: 'Display all available commands',
    group: 'System',
    aliases: ['--list']
)]
class ListCommand implements CommandInterface
{
    use CommandsBuilderTrait;

    /**
     * @inheritDoc
     */
    public function handle(?string $argument): void
    {
        $this->printHeader();
        $this->printTable();
    }
}