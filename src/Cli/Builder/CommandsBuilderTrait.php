<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Builder;

use Asterios\Core\Cli\Commands\AboutCommand;
use Asterios\Core\Cli\Commands\ListCommand;
use Asterios\Core\Cli\Commands\MakeModelCommand;

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

    /**
     * @param array<string, string|int|null> $rows
     * @param string $name
     * @return void
     */
    public function printTable(array $rows, string $name = 'asterios'): void
    {
        foreach ($rows as $command => $value)
        {
            $commandName = (empty($name)) ? $command : $name . ' ' . $command;
            $description = method_exists($value, 'description') ? $value::description() : $value;

            $this->printPrettyCommand($commandName, $description);
        }
        echo "\n";
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
     * @return string[]
     */
    public function commands(): array
    {
        return [
            'make:model' => MakeModelCommand::class,
            'about' => AboutCommand::class,
            'list' => ListCommand::class,
            'help' => ListCommand::class,
        ];
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

}