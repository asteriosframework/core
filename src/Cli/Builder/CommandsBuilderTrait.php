<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Builder;

use Asterios\Core\Cli\CommandRegistry;

trait CommandsBuilderTrait
{
    /**
     * @return void
     */
    public function printHeader(): void
    {
        $text = 'AsteriosPHP CLI';
        echo str_repeat("=", mb_strlen($text)) . "\n";
        echo "\033[1;35m$text\033[0m\n";
        echo str_repeat("=", mb_strlen($text)) . "\n\n";
    }

    public function printTable(string $prefix = 'asterios'): void
    {
        $commands = CommandRegistry::all();

        // 1. Gruppieren
        $grouped = [];

        foreach ($commands as $cmd)
        {
            $group = $cmd['group'] ?? 'Allgemein';
            $grouped[$group][] = $cmd;
        }

        foreach ($grouped as &$cmds)
        {
            usort($cmds, fn($a, $b) => strcmp($a['name'], $b['name']));
        }
        unset($cmds);

        // 3. Ausgabe
        foreach ($grouped as $groupName => $cmds)
        {
            echo "\033[1;36m$groupName:\033[0m\n";

            foreach ($cmds as $cmd)
            {
                $aliases = !empty($cmd['aliases']) ? ' (' . implode(', ', $cmd['aliases']) . ')' : '';
                $fullCommand = (empty($prefix) ? '' : "$prefix ") . $cmd['name'] . $aliases;
                $this->printPrettyCommand($fullCommand, $cmd['description'] ?? '');
            }

            echo PHP_EOL;
        }
    }

    /**
     * @param string $message
     * @param string $context
     * @return void
     */
    public function printError(string $message, string $context = ''): void
    {
        echo "\033[1;31m$message\033[0m $context\n\n";
    }

    /**
     * @param array<string, array<string, string|int|float|bool|null>> $groups
     */
    public function printDataTable(array $groups): void
    {
        foreach ($groups as $group => $rows)
        {
            echo "\033[1;36m$group:\033[0m\n"; // Cyan Gruppe-Überschrift

            foreach ($rows as $label => $value)
            {
                $emoji = $this->detectEmoji($label, $value);
                $valueStr = $this->formatFancyValue($value);
                $this->printPrettyRow("$emoji $label", $valueStr);
            }

            echo "\n";
        }
    }

    private function printPrettyRow(string $label, string $value): void
    {
        $label = trim($label);
        $totalWidth = 45;
        $dots = str_repeat('.', max(1, $totalWidth - strlen(strip_tags($label))));
        echo "  \033[1;33m$label\033[0m $dots $value\n";
    }

    private function formatFancyValue(mixed $value): string
    {
        return match (true)
        {
            is_bool($value) => $value ? "\033[1;32m✔ yes\033[0m" : "\033[1;31m✘ no\033[0m",
            is_null($value) => "\033[1;90m–\033[0m",
            default => "\033[0m" . $value,
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

    /**
     * @param string $command
     * @param string $description
     * @return void
     */
    private function printPrettyCommand(string $command, string $description): void
    {
        $totalWidth = 40;
        $dots = str_repeat('.', max(1, $totalWidth - strlen($command)));
        echo "  \033[1;32m$command\033[0m $dots $description\n";
    }

    public function printListTable(string $title, array $items, string $keyField, string $valueField): void
    {
        echo "\033[1;36m$title:\033[0m\n";

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
            echo "  \033[1;33m$key\033[0m $dots $value\n";
        }

        echo PHP_EOL;
    }
}