<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Interfaces\CommandInterface;

#[Command(
    name: 'make:model',
    description: 'Create a new model class. Use --namespace= for optional namespace.',
    group: 'Make',
    aliases: ['--mm']
)]
class MakeModelCommand implements CommandInterface
{
    use CommandsBuilderTrait;

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
                $modelNamespace = substr($arg, strlen('--namespace='));
                break;
            }
        }

        $modelPath = match ($modelNamespace)
        {
            'Asterios\Cms\Models' => 'Models',
            default => str_replace(['App\\', '\\'], ['', '/'], $modelNamespace),
        };

        $relativePath = $modelPath;
        $basePath = rtrim(str_replace('/public', '', $_SERVER['DOCUMENT_ROOT']), '/') . '/app';
        $directory = $basePath . '/' . $relativePath;

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory))
        {
            throw new \RuntimeException(sprintf('Model directory "%s" was not created', $directory));
        }

        $filename = $directory . '/' . $modelName . '.php';

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

        echo "✅ Model \033[1;32m{$modelNamespace}\\{$modelName}\033[0m created at \033[0;36m{$filename}\033[0m\n";
    }
}
