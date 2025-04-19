<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Interfaces\CommandInterface;

class ListCommand implements CommandInterface
{
    use CommandsBuilderTrait;

    /**
     * @inheritDoc
     */
    public function handle(?string $argument): void
    {
        $this->printHeader();
        $this->printTable($this->commands());
    }

    /**
     * @inheritDoc
     */
    public static function description(): string
    {
        return 'List available commands';
    }
}