<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;
use Asterios\Core\Interfaces\CommandInterface;

#[Command(
    name: 'make:model',
    description: 'Create a new model class',
    group: 'Make',
    aliases: ['--mm']
)]
class MakeModelCommand implements CommandInterface
{
    use CommandsBuilderTrait;

    public function handle(?string $argument): void
    {
        $this->printHeader();

        if (!$argument)
        {
            $this->printError('Missing model name.');
            echo "Example: asterios make:model Users\n";

            return;
        }

        $modelName = ucfirst($argument);

        $protectedDirectory = str_replace('/public', '', $_SERVER['DOCUMENT_ROOT']);
        $appModelDirectory = $protectedDirectory . 'app/Models/';

        if (!is_dir($appModelDirectory) && !mkdir($appModelDirectory, 0755, true) && !is_dir($appModelDirectory))
        {
            throw new \RuntimeException(sprintf('Model directory "%s" was not created', $appModelDirectory));
        }

        $filename = $appModelDirectory . $argument . '.php';

        if (file_exists($filename))
        {
            echo "Model '{$argument}' already exists.\n";

            return;
        }

        file_put_contents($filename,
            "<?php declare(strict_types=1);\n\nuse Asterios\Core\Model;\n\nclass {$modelName} extends Model\n{\n\n}\n");

        echo "Model '{$argument}' created.\n";
    }
}