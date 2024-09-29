<?php declare(strict_types=1);

namespace Asterios\Core\Db;

use Asterios\Core\Asterios;
use Asterios\Core\Db;
use Asterios\Core\Dto\DbMigrationDto;
use Asterios\Core\Env;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Logger;

class Migration
{
    /**
     * @var string[] $errors
     */
    protected array $errors = [];
    protected string $envFile = '.env';

    protected Env|null $env;

    public function __construct(string $envFile = '.env')
    {
        $this->envFile = $envFile;

        $this->env = (new Env($this->envFile));
    }

    public function migrate(DbMigrationDto $dto): bool
    {
        $migrationPath = $this->getMigrationsPath();

        if (null === $migrationPath)
        {
            Logger::forge()
                ->error('Database migration failed: Could not load migration path.');

            $this->errors[] = 'Database migration failed!';

            return false;
        }

        foreach ($dto->getTablesToMigrate() as $table)
        {
            $foreignKeys = [];

            if (array_key_exists($table, $dto->getForeignKeysToMigrate()))
            {
                $foreignKeys[$table] = $dto->getForeignKeysToMigrate()[$table];
            }

            try
            {
                Db::forge()
                    ->migrate($table, $foreignKeys, $dto->dropTables(), $migrationPath);
            }
            catch (ConfigLoadException $e)
            {
                Logger::forge()
                    ->error('Database migration failed: ' . $e->getMessage());

                $this->errors[] = 'Database seeder failed! Check logfile for details.';

                return false;
            }
        }

        return true;
    }

    public function seed(DbMigrationDto $dto): bool
    {
        $seederPath = $this->getSeederPath();

        if (null === $seederPath)
        {
            Logger::forge()
                ->error('Database seeder failed: Could not load seeder path.');

            $this->errors[] = 'Database seeder failed!';

            return false;
        }

        foreach ($dto->getSeeder() as $table)
        {
            try
            {
                Db::forge()
                    ->seed($table, $dto->truncateTables(), $seederPath);
            }
            catch (ConfigLoadException $e)
            {
                Logger::forge()
                    ->error('Database seeder failed: ' . $e->getMessage());

                $this->errors[] = 'Database seeder failed! Check logfile for details.';

                return false;
            }
        }

        return true;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function getPathsFromEnv(string $key): string|null
    {
        try
        {
            return $this->env->get($key);
        }
        catch (EnvException|EnvLoadException $e)
        {

            Logger::forge()
                ->error('Could not load variable "' . $key . '" from env file!', ['envFile' => $this->envFile, 'exception' => $e->getMessage()]);

            $this->errors[] = 'Could not load variable "' . $key . '" from env file "' . $this->envFile . '"!';

            return null;
        }
    }

    protected function getMigrationsPath(): string|null
    {
        $migrationPath = $this->getPathsFromEnv('DATABASE_MIGRATION_PATH');

        return $this->getProtectedPath() . $migrationPath ?? null;
    }

    protected function getSeederPath(): string|null
    {
        $seederPath = $this->getPathsFromEnv('DATABASE_SEEDER_PATH');

        return $this->getProtectedPath() . $seederPath ?? null;
    }

    protected function getProtectedPath(): string
    {
        return str_replace('/public', '', Asterios::getDocumentRoot());
    }
}