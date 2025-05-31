<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Base;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Builder\ColorBuilder;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Cli\Support\ArgumentParserTrait;
use Asterios\Core\Contracts\CommandInterface;

abstract class BaseCommand implements CommandInterface
{
    use CommandsBuilderTrait;
    use ArgumentParserTrait;

    // @codeCoverageIgnoreStart
    public function __construct()
    {
        $this->parseArguments();
    }

    // @codeCoverageIgnoreEnd

    abstract public function handle(?string $argument): void;

    /**
     * @return void
     */
    protected function printCommandHelpFromAttribute(): void
    {
        $reflection = new \ReflectionClass($this);
        $attributes = $reflection->getAttributes(Command::class);

        // @codeCoverageIgnoreStart
        if (empty($attributes))
        {
            echo 'No help available.' . PHP_EOL;

            return;
        }
        // @codeCoverageIgnoreEnd

        /** @var Command $command */
        $command = $attributes[0]->newInstance();

        echo $this->color()
                ->cyan()
                ->apply('Command:     ') . $this->color()
                ->yellow()
                ->apply($command->name) . PHP_EOL;
        echo $this->color()
                ->cyan()
                ->apply('Description: ') . $command->description . PHP_EOL . PHP_EOL;

        if (!empty($command->options))
        {
            echo $this->color()
                    ->cyan()
                    ->apply('Options:') . PHP_EOL;

            $maxLength = max(array_map(fn ($key) => mb_strlen($key), array_keys($command->options)));

            foreach ($command->options as $opt => $desc)
            {
                $dots = str_repeat('.', max(1, ($maxLength + 4) - mb_strlen($opt)));
                echo "  {$opt} {$dots} {$desc}" . PHP_EOL;
            }

            echo PHP_EOL;
        }
    }

    /**
     * @return ColorBuilder
     */
    private function color(): ColorBuilder
    {
        return $this->colorBuilder ??= new ColorBuilder();
    }
}
