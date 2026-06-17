<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Env;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Execution\PathResolver;

#[Command(
    name: 'make:seeder',
    description: 'Create a new json seeder file',
    group: 'Make',
    aliases: ['--ms']
)]
class MakeSeederCommand extends BaseCommand
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

        if (!$argument)
        {
            $this->printError('Missing seeder name.');
            echo "Example: asterios make:seeder users\n";

            return;
        }

        $seederName = strtolower($argument);

        $env = new Env(getcwd() . DIRECTORY_SEPARATOR . '.env');
        $pathResolver = new PathResolver($env);
        $appSeederDirectory = getcwd() . $pathResolver->seeders();

        $this->ensureDirectoryExists($appSeederDirectory);

        $filename = $appSeederDirectory . $seederName . '.json';

        if ($this->fileExists($filename))
        {
            echo "⚠️  Seeder '{$seederName}' already exists.\n";

            return;
        }

        $this->writeFile($filename, "[]\n");

        echo "✅  Seeder '{$seederName}' created.\n";
    }
}
