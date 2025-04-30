<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Builder;

use Asterios\Core\Asterios;
use Asterios\Core\Cli\CommandRegistry;

trait CommandsBuilderTrait
{
    protected ColorBuilder $colorBuilder;

    public function printHeader(): void
    {
        $text = Asterios::NAME . ' CLI';
        echo str_repeat('=', mb_strlen($text)) . PHP_EOL;
        echo $this->color()
            ->magenta()
            ->apply($text . PHP_EOL);
        echo str_repeat('=', mb_strlen($text)) . PHP_EOL . PHP_EOL;
    }

    public function printTable(string $prefix = 'asterios'): void
    {
        $registeredCommands = CommandRegistry::all();
        $grouped = [];

        foreach ($registeredCommands as $registeredCommand)
        {
            $group = $registeredCommand['group'] ?? 'General';
            $grouped[$group][] = $registeredCommand;
        }

        foreach ($grouped as &$commands)
        {
            usort($commands, static fn($a, $b) => strcmp($a['name'], $b['name']));
        }
        unset($commands);

        foreach ($grouped as $groupName => $commands)
        {
            echo $this->color()
                ->cyan()
                ->apply($groupName . ':' . PHP_EOL);

            foreach ($commands as $cmd)
            {
                $aliases = !empty($cmd['aliases']) ? ' (' . implode(', ', $cmd['aliases']) . ')' : '';
                $fullCommand = (empty($prefix) ? '' : "$prefix ") . $cmd['name'] . $aliases;
                $this->printPrettyCommand($fullCommand, $cmd['description'] ?? '');
            }

            echo PHP_EOL;
        }
    }

    public function printError(string $message, string $context = ''): void
    {
        echo $this->color()
            ->red()
            ->apply($message . ' ' . $context . PHP_EOL . PHP_EOL);
    }

    public function printDataTable(array $groups): void
    {
        foreach ($groups as $group => $rows)
        {
            echo $this->color()
                ->cyan()
                ->apply($group . ':' . PHP_EOL);

            foreach ($rows as $label => $value)
            {
                $emoji = $this->detectEmoji($label, $value);
                $valueStr = $this->formatFancyValue($value);
                $this->printPrettyRow($emoji . ' ' . $label, $valueStr);
            }

            echo PHP_EOL;
        }
    }

    public function printListTable(string $title, array $items, string $keyField, string $valueField): void
    {
        echo $this->color()
            ->cyan()
            ->apply($title . ':' . PHP_EOL);

        $maxKeyLength = 0;

        foreach ($items as $item)
        {
            if (isset($item[$keyField]))
            {
                $length = mb_strlen((string)$item[$keyField]);
                if ($length > $maxKeyLength)
                {
                    $maxKeyLength = $length;
                }
            }
        }

        $totalWidth = $maxKeyLength + 10;

        foreach ($items as $item)
        {
            $key = $item[$keyField] ?? '';
            $value = $item[$valueField] ?? '';
            $dots = str_repeat('.', max(1, $totalWidth - mb_strlen($key)));
            echo "  " . $this->color()
                    ->yellow()
                    ->apply($key . ' ' . $dots . ' ' . $value . PHP_EOL);
        }

        echo PHP_EOL;
    }

    private function printPrettyCommand(string $command, string $description): void
    {
        $totalWidth = 40;
        $dots = str_repeat('.', max(1, $totalWidth - mb_strlen($command)));
        echo "  " . $this->color()
                ->white()
                ->apply($command . ' ' . $dots . ' ' . $description . PHP_EOL);
    }

    private function printPrettyRow(string $label, string $value): void
    {
        $label = trim($label);
        $totalWidth = 45;
        $dots = str_repeat('.', max(1, $totalWidth - mb_strlen(strip_tags($label))));
        echo "  " . $this->color()
                ->white()
                ->apply($label . ' ' . $dots . ' ' . $value . PHP_EOL);
    }

    private function formatFancyValue(mixed $value): string
    {
        return match (true)
        {
            is_bool($value) => $value ? $this->color()
                ->green()
                ->apply('✔ yes') : $this->color()
                ->red()
                ->apply('✘ no'),
            is_null($value) => $this->color()
                ->gray()
                ->apply('–'),
            default => (string)$value,
        };
    }

    private function detectEmoji(string $label, mixed $value): string
    {
        $label = strtolower($label);

        return match (true)
        {
            str_contains($label, 'version') => '🛠',
            str_contains($label, 'debug') => $value ? '🐞' : '✅',
            str_contains($label, 'cache') => '🗃',
            str_contains($label, 'env') => '🌍',
            str_contains($label, 'php') => '🐘',
            str_contains($label, 'db') => '🛢',
            default => '🛠',
        };
    }

    private function color(): ColorBuilder
    {
        return $this->colorBuilder ??= new ColorBuilder;
    }
}