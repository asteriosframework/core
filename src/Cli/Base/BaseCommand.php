<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Base;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Cli\Support\ArgumentParserTrait;
use Asterios\Core\Interfaces\CommandInterface;

abstract class BaseCommand implements CommandInterface
{
    use CommandsBuilderTrait;
    use ArgumentParserTrait;

    public function __construct()
    {
        $this->parseArguments();
    }

    abstract public function handle(?string $argument): void;

    protected function printCommandHelpFromAttribute(): void
    {
        $reflection = new \ReflectionClass($this);
        $attributes = $reflection->getAttributes(Command::class);

        if (empty($attributes))
        {
            echo "No help available." . PHP_EOL;

            return;
        }

        /** @var Command $command */
        $command = $attributes[0]->newInstance();

        echo "Command:     " . $command->name . PHP_EOL;
        echo "Description: " . $command->description . PHP_EOL . PHP_EOL;

        if (!empty($command->options))
        {
            echo "Options:" . PHP_EOL;

            // Maximale Länge der Optionsnamen berechnen
            $maxLength = max(array_map(fn($key) => mb_strlen($key), array_keys($command->options)));

            foreach ($command->options as $opt => $desc)
            {
                $dots = str_repeat('.', max(1, ($maxLength + 4) - mb_strlen($opt)));
                echo "  {$opt} {$dots} {$desc}" . PHP_EOL;
            }

            echo PHP_EOL;
        }
    }
}
