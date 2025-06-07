<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;

#[Command(
    name: 'make:model',
    description: 'Create a new model class. Use --namespace= for optional namespace.',
    group: 'Make',
    aliases: ['--mm']
)]
class MakeModelCommand extends BaseCommand
{
    public function handle(?string $argument): void
    {
        $this->printHeader();

        global $argv;

        if (!$argument)
        {
            $this->printError('Missing model name.');
            echo "Example: asterios make:model User --namespace=My\\Namespace\\Models\n";

            return;
        }

        $modelName = ucfirst($argument);

        $modelNamespace = 'App\\Models';

        foreach ($argv as $arg)
        {
            if (str_starts_with($arg, '--namespace='))
            {
                $modelNamespace = $this->stringToNamespace(substr($arg, strlen('--namespace=')));
                break;
            }
        }

        $protectedDirectory = str_replace('/public', '', $_SERVER['DOCUMENT_ROOT']);
        $appModelDirectory = $protectedDirectory . 'app/Models/';

        if (!is_dir($appModelDirectory) && !mkdir($appModelDirectory, 0755, true) && !is_dir($appModelDirectory))
        {
            throw new \RuntimeException(sprintf('Model directory "%s" was not created', $appModelDirectory));
        }

        $filename = $appModelDirectory . $modelName . '.php';

        if (file_exists($filename))
        {
            echo "⚠️  Model '{$modelName}' already exists at \033[0;36m{$filename}\033[0m\n";

            return;
        }

        $template = <<<PHP
<?php declare(strict_types=1);

namespace {$modelNamespace};

use Asterios\Core\Model;

class {$modelName} extends Model
{

}

PHP;

        file_put_contents($filename, $template);

        echo "✅  Model \033[1;32m{$modelNamespace}\\{$modelName}\033[0m created at \033[0;36m{$filename}\033[0m\n";
    }

    private function stringToNamespace(string $input): string
    {
        $parts = preg_split('/(?=[A-Z])/', $input, -1, PREG_SPLIT_NO_EMPTY);

        return implode('\\', $parts);
    }
}
