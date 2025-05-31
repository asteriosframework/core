<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Asterios\Core\Cli\Builder\CommandsBuilderTrait;

#[Command(
    name: 'make:migration',
    description: 'Create a new migration class',
    group: 'Make',
    aliases: ['--mmi']
)]
class MakeMigrationCommand extends BaseCommand
{
    use CommandsBuilderTrait;

    public function handle(?string $argument): void
    {
        $this->printHeader();

        if (!$argument)
        {
            $this->printError('Missing migration name.');
            echo "Example: asterios make:migration create_users_table\n";

            return;
        }

        $protectedDirectory = str_replace('/public', '', $_SERVER['DOCUMENT_ROOT']);
        $appMigrationDirectory = $protectedDirectory . 'database/migrations/';

        $formattedName = strtolower(preg_replace('/\W+/', '_', $argument));
        $timestamp = date('Y_m_d_His');

        $filename = "{$timestamp}_{$formattedName}.php";

        if (!is_dir($appMigrationDirectory) && !mkdir($appMigrationDirectory, 0777, true) && !is_dir($appMigrationDirectory))
        {
            throw new \RuntimeException(sprintf('Migration directory "%s" was not created', $appMigrationDirectory));
        }

        $filepath = $appMigrationDirectory . $filename;

        $schemaAction = $this->getSchemaAction($argument);
        $tableName = $this->getTableName($argument);

        $schemaBlueprintUp = match ($schemaAction)
        {
            'create' => "Schema::create('$tableName', static function (SchemaBuilder " . '$table' . ") {\n\n        });",
            'update' => "Schema::table('$tableName', static function (SchemaBuilder " . '$table' . ") {\n\n         });",
            default => '',
        };

        $schemaBlueprintDown = match ($schemaAction)
        {
            'create' => "Schema::drop('$tableName');",
            default => '',
        };

        $content = <<<PHP
<?php declare(strict_types=1);

use Asterios\Core\Db\Builder\SchemaBuilder;
use Asterios\Core\Db\Migration\Schema;

return new class {
    public function up(): void
    {
        // TODO: Add migration logic for: $formattedName
        $schemaBlueprintUp

    }

    public function down(): void
    {
        // TODO: Revert migration logic for: $formattedName
        $schemaBlueprintDown
    }
};
PHP;

        file_put_contents($filepath, $content);

        echo "✅  Migration created: $filepath\n";
    }

    private function getSchemaAction(string $input): string
    {
        $parts = explode('_', $input);

        return $parts[0];
    }

    private function getTableName(string $input): string
    {
        $parts = explode('_', $input);

        array_shift($parts);

        if (end($parts) === 'table')
        {
            array_pop($parts);
        }

        return implode('_', $parts);
    }
}
