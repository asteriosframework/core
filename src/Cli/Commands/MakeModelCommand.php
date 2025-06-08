<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Cli\Support\ArgumentParserTrait;

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

        $argument ??= $this->getPositional(0);

        if (!$argument)
        {
            $this->printError('Missing model name.');
            echo "Example: asterios make:model User --namespace=My\\Namespace\\Models\n";

            return;
        }

        $modelName = ucfirst($argument);
        $modelNamespace = $this->getValue('--namespace') ?? 'App\\Models';

        $protectedDirectory = str_replace('/public', '', $_SERVER['DOCUMENT_ROOT']);
        $appModelDirectory = $protectedDirectory . 'app/Models/';

        $this->ensureDirectoryExists($appModelDirectory);

        $filename = $appModelDirectory . $modelName . '.php';

        if ($this->fileExists($filename))
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

        $this->writeFile($filename, $modelName);

        echo "✅  Model \033[1;32m{$modelNamespace}\\{$modelName}\033[0m created at \033[0;36m{$filename}\033[0m\n";
    }

    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    /** @codeCoverageIgnoreSart */
    private function stringToNamespace(string $input): string
    {
        $parts = preg_split('/(?=[A-Z])/', $input, -1, PREG_SPLIT_NO_EMPTY);
        return implode('\\', $parts);
    }
    /** @codeCoverageIgnoreEnd  */
}
