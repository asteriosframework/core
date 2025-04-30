<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Db\Seeder;
use Asterios\Core\Interfaces\CommandInterface;

#[Command(
    name: 'make:seeder',
    description: 'Create a new json seeder file',
    group: 'Make',
    aliases: ['--ms']
)]
class MakeSeederCommand implements CommandInterface
{
    use CommandsBuilderTrait;

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

        $seeder = new Seeder();
        $appSeederDirectory = $seeder->getSeederPath();

        if (!is_dir($appSeederDirectory) && !mkdir($appSeederDirectory, 0755, true) && !is_dir($appSeederDirectory))
        {
            throw new \RuntimeException(sprintf('Seeder directory "%s" was not created', $appSeederDirectory));
        }

        $filename = $appSeederDirectory . $seederName . '.json';

        if (file_exists($filename))
        {
            echo "⚠️  Seeder '{$seederName}' already exists.\n";

            return;
        }

        file_put_contents($filename,
            "[]\n");

        echo "✅  Seeder '{$seederName}' created.\n";
    }
}