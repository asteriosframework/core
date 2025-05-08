<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Support;

trait ArgumentParserTrait
{
    protected array $args = [];

    protected function parseArguments(array $argv = null): void
    {
        $argv ??= $_SERVER['argv'] ?? [];
        array_shift($argv);
        $this->args = $argv;
    }

    protected function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->args, true);
    }

    protected function getValue(string $key): ?string
    {
        foreach ($this->args as $arg)
        {
            if (str_starts_with($arg, "$key="))
            {
                return substr($arg, strlen($key) + 1);
            }
        }

        return null;
    }

    protected function getPositional(int $index): ?string
    {
        $positional = array_values(array_filter(
            $this->args,
            static fn($arg) => !str_starts_with($arg, '--')
        ));

        return $positional[$index] ?? null;
    }

    protected function allArgs(): array
    {
        return $this->args;
    }
}
