<?php declare(strict_types=1);

namespace Asterios\Test\Cli\Fixtures;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Contracts\CommandInterface;

#[Command(name: 'test:example', description: 'An example command', group: 'testing', aliases: ['t:e'])]
class MockCommand implements CommandInterface
{
    public function handle(?string $argument): void
    {
    }
}
