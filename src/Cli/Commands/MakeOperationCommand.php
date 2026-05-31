<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Env;
use Asterios\Core\Execution\PathResolver;

#[Command(
    name: 'make:operation',
    description: 'Create a new operation class',
    group: 'Make',
    aliases: ['--mop']
)]
class MakeOperationCommand extends BaseCommand
{
    public function handle(?string $argument): void
    {
        $this->printHeader();

        if (!$argument)
        {
            $this->printError('Missing operation name.');
            echo "Example: asterios make:operation import_legacy_users\n";

            return;
        }

        $env = new Env(getcwd() . DIRECTORY_SEPARATOR . '.env');
        $pathResolver = new PathResolver($env);
        $operationDirectory = getcwd() . $pathResolver->resolve('OPERATION_PATH');

        $formattedName = strtolower(
            preg_replace('/\W+/', '_', $argument)
        );

        $timestamp = date('Y_m_d_His');

        $filename = sprintf(
            '%s_%s.php',
            $timestamp,
            $formattedName
        );

        $this->ensureDirectoryExists(
            $operationDirectory
        );

        $filepath = $operationDirectory . $filename;

        $content = <<<PHP
<?php declare(strict_types=1);

use Asterios\Core\Contracts\Operation\OperationInterface;

return new class implements OperationInterface {

    public function run(): void
    {
        // TODO: Add operation logic for: {$formattedName}
    }
};
PHP;

        $this->writeFile(
            $filepath,
            $content
        );

        echo "✅  Operation created: {$filepath}\n";
    }
}
