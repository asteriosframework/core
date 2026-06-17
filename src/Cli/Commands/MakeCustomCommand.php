<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Asterios;
use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Env;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Execution\PathResolver;

#[Command(
    name: 'make:command',
    description: 'Create a new custom CLI command',
    group: 'Make',
    aliases: ['--mc'],
    options: [
        '--help' => 'Show command help',
    ],
)]
class MakeCustomCommand extends BaseCommand
{
    /**
     * @param string|null $argument
     * @return void
     * @throws EnvException
     * @throws EnvLoadException
     */
    public function handle(?string $argument): void
    {
        $this->printHeader();

        if ($this->hasFlag('--help'))
        {
            $this->printCommandHelpFromAttribute();

            return;
        }

        if (!$argument)
        {
            $this->printError('Missing command class name.');
            echo "Example: asterios make:command custom\n";

            return;
        }

        $className = $this->normalizeClassName($argument);

        $env = new Env(getcwd() . DIRECTORY_SEPARATOR . '.env');
        $pathResolver = new PathResolver($env);
        $directory = getcwd() . $pathResolver->commands();

        $this->ensureDirectoryExists($directory);

        $filename = $directory . $className . '.php';

        if ($this->fileExists($filename))
        {
            echo "⚠️  Command '{$className}' already exists.\n";

            return;
        }

        $this->writeFile(
            $filename,
            $this->buildCommandStub($className)
        );

        echo "✅  Command '{$className}' created.\n";
        echo "📍  {$filename}\n";
    }

    private function normalizeClassName(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9]/', '', $name);

        if (!str_ends_with($name, 'Command'))
        {
            $name .= 'Command';
        }

        return ucfirst($name);
    }

    private function getCommandDirectory(): string
    {
        $env = new Env(
            Asterios::getBasePath() . DIRECTORY_SEPARATOR . '.env'
        );

        $path = $env->get('CLI_COMMAND_PATH', 'app/Cli/Commands');

        return Asterios::getBasePath(
            ltrim($path, DIRECTORY_SEPARATOR)
        );
    }

    private function buildCommandStub(string $className): string
    {
        $commandName = strtolower(
            preg_replace('/Command$/', '', $className)
        );

        $commandName = preg_replace('/(?<!^)[A-Z]/', ':$0', $commandName);
        $commandName = strtolower($commandName);

        return <<<PHP
<?php declare(strict_types=1);

namespace App\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;

#[Command(
    name: '{$commandName}',
    description: 'Describe your command',
    group: 'Custom',
    aliases: [],
    options: [
        '--help' => 'Show command help',
    ],
)]
class {$className} extends BaseCommand
{
    public function handle(?string \$argument): void
    {
        \$this->printHeader();

        if (\$this->hasFlag('--help'))
        {
            \$this->printCommandHelpFromAttribute();

            return;
        }

        echo "Hello from {$className}!\\n";

        if (\$argument)
        {
            echo "Argument: {\$argument}\\n";
        }
    }
}

PHP;
    }
}
