<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Builder;

use Asterios\Core\Asterios;
use Asterios\Core\Cli\CommandRegistry;
use Asterios\Core\Contracts\Cli\CommandRegistryInterface;

/** @codeCoverageIgnore */
trait CommandsBuilderTrait
{
    protected ColorBuilder $colorBuilder;
    protected ?CommandRegistryInterface $commandRegistry = null;

    protected function printHeader(): void
    {
        $text = Asterios::NAME . ' CLI';
        echo str_repeat('=', mb_strlen($text)) . PHP_EOL;
        echo $this->color()
            ->magenta()
            ->apply($text . PHP_EOL);
        echo str_repeat('=', mb_strlen($text)) . PHP_EOL . PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    protected function printTable(string $prefix = 'asterios'): void
    {
        $registeredCommands = $this->getRegisteredCommands();
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

    /**
     * @inheritDoc
     */
    protected function printError(string $message, string $context = ''): void
    {
        $symbol = '❌ ERROR';
        $line = str_repeat('─', 60);

        echo PHP_EOL;
        echo $this->color()
                ->red()
                ->bold()
                ->apply($symbol) . PHP_EOL;
        echo $this->color()
                ->red()
                ->apply($line) . PHP_EOL;
        echo $this->color()
                ->red()
                ->apply($message) . PHP_EOL;

        if (!empty($context))
        {
            echo PHP_EOL . $this->color()
                    ->gray()
                    ->apply(trim($context)) . PHP_EOL;
        }

        echo $this->color()
                ->red()
                ->apply($line) . PHP_EOL . PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    protected function printDataTable(array $groups): void
    {
        foreach ($groups as $group => $rows)
        {
            $totalWidth = 80;
            $dots = ' ' . str_repeat('.', max(1, $totalWidth - mb_strlen(strip_tags($group))));

            echo $this->color()
                    ->green()
                    ->apply($group) . $dots . PHP_EOL;

            $maxLabelLength = 0;
            $processedRows = [];

            foreach ($rows as $label => $value)
            {
                $emoji = $this->detectEmoji($label, $value);
                $fullLabel = $emoji . ' ' . $label;

                $labelLength = mb_strwidth(strip_tags($fullLabel));
                if ($labelLength > $maxLabelLength)
                {
                    $maxLabelLength = $labelLength;
                }

                $processedRows[] = [
                    'label' => $fullLabel,
                    'value' => $this->formatFancyValue($value),
                ];
            }

            foreach ($processedRows as $row)
            {
                $this->printPrettyRowAligned($row['label'], $row['value'], $maxLabelLength);
            }

            echo PHP_EOL;
        }
    }

    /**
     * @inheritDoc
     */
    protected function printListTable(string $title, array $items, string $keyField, string $valueField): void
    {
        echo $this->color()
            ->cyan()
            ->apply($title . ':' . PHP_EOL);

        $maxKeyLength = 0;
        $processedItems = [];

        foreach ($items as $item)
        {
            $key = (string)($item[$keyField] ?? '');
            $value = (string)($item[$valueField] ?? '');

            $keyLength = mb_strwidth(strip_tags($key));
            if ($keyLength > $maxKeyLength)
            {
                $maxKeyLength = $keyLength;
            }

            $processedItems[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        foreach ($processedItems as $item)
        {
            $this->printPrettyRowAligned($item['key'], $item['value'], $maxKeyLength);
        }

        echo PHP_EOL;
    }

    /**
     * @param string $command
     * @param string $description
     * @return void
     */
    private function printPrettyCommand(string $command, string $description): void
    {
        $command = trim(strip_tags($command));
        $description = trim(strip_tags($description));

        $labelWidth = 40;

        $actualCommandWidth = mb_strwidth($command);
        $dotsWidth = max(1, $labelWidth - $actualCommandWidth + 2);

        $dots = str_repeat('.', $dotsWidth);

        echo "  " . $this->color()
                ->white()
                ->apply($command . ' ' . $dots)
            . ' ' . $this->formatFancyValue($description) . PHP_EOL;
    }

    /**
     * @param string $label
     * @param string $value
     * @return void
     * @codeCoverageIgnore
     */
    private function printPrettyRow(string $label, string $value): void
    {
        $label = trim($label);
        $totalWidth = 80;
        $dots = str_repeat('.', max(1, $totalWidth - mb_strlen(strip_tags($label))));
        echo "  " . $this->color()
                ->white()
                ->apply($label . ' ' . $dots . ' ' . $value . PHP_EOL);
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function formatFancyValue(mixed $value): string
    {
        return match (true)
        {
            is_bool($value) => $value
                ? $this->color()
                    ->green()
                    ->apply('✔ yes')
                : $this->color()
                    ->red()
                    ->apply('✘ no'),

            is_null($value) => $this->color()
                ->gray()
                ->apply('–'),

            is_string($value) => match (strtolower($value))
            {
                'production' => $this->color()
                    ->green()
                    ->apply($value),
                'development' => $this->color()
                    ->yellow()
                    ->apply($value),
                'testing', 'test' => $this->color()
                    ->cyan()
                    ->apply($value),
                'local', 'staging' => $this->color()
                    ->magenta()
                    ->apply($value),
                default => $value,
            },

            default => (string)$value,
        };
    }

    /**
     * @param string $label
     * @param mixed $value
     * @return string
     */
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
     * @return ColorBuilder
     */
    private function color(): ColorBuilder
    {
        return $this->colorBuilder ??= new ColorBuilder();
    }

    private function printPrettyRowAligned(string $label, string $value, int $maxLabelWidth): void
    {
        $label = trim(strip_tags($label));
        $value = trim(strip_tags($value));

        $valueColumn = 60;

        $labelWidth = mb_strwidth($label);
        $dotsWidth = max(2, $valueColumn - $labelWidth - 2);
        $dots = str_repeat('.', $dotsWidth);

        echo "  " . $this->color()
                ->white()
                ->apply($label . ' ' . $dots)
            . '  ' . $this->color()
                ->white()
                ->apply($value . PHP_EOL);
    }

    protected function setCommandRegistry(CommandRegistryInterface $registry): void
    {
        $this->commandRegistry = $registry;
    }

    protected function getRegisteredCommands(): array
    {
        return ($this->commandRegistry ?? new CommandRegistry())->all();
    }

    protected function generateFile(string $targetPath, string $stubPath, array $replacements): bool
    {
        if ($this->fileExists($stubPath))
        {
            return false;
        }

        $content = $this->readFile($stubPath);

        foreach ($replacements as $search => $replace)
        {
            $content = str_replace($search, $replace, $content);
        }

        return $this->writeFile($targetPath, $content);
    }

    protected function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    protected function readFile(string $path): string
    {
        return file_get_contents($path);
    }

    protected function writeFile(string $path, string $content): bool
    {
        return file_put_contents($path, $content) !== false;
    }
}
