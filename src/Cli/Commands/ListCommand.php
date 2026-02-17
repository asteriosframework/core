<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;

#[Command(
    name: 'list',
    description: 'Display all available commands',
    group: 'System',
    aliases: ['--list']
)]
class ListCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function handle(?string $argument): void
    {
        $this->printHeader();
        $this->printTable();
    }
}
