<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Asterios;
use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Env;
use Asterios\Core\Execution\PathResolver;

#[Command(
    name: 'make:command',
    description: 'Create a new custom CLI command',
    group: 'Make',
    aliases: ['--mc']
)]
class MakeCustomCommand extends BaseCommand
{
    public function handle(?string $argument): void
    {
        $this->printHeader();

        if (!$argument)
        {
            $this->printError('Missing command class name.');
            echo "Example: asterios make:command custom\n";

            return;
        }

        $className = $this->normalizeClassName($argument);

        $env = new Env(getcwd() . DIRECTORY_SEPARATOR . '.env');
        $pathResolver = new PathResolver($env);
        $directory = getcwd() . $pathResolver->resolve('CLI_COMMAND_PATH');

        $this->ensureDirectoryExists($directory);

        $filename = $directory . DIRECTORY_SEPARATOR . $className . '.php';

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
    aliases: []
)]
class {$className} extends BaseCommand
{
    public function handle(?string \$argument): void
    {
        \$this->printHeader();

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
