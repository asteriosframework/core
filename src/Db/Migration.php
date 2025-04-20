<?php declare(strict_types=1);

namespace Asterios\Core\Db;

use Asterios\Core\Asterios;
use Asterios\Core\Db;
use Asterios\Core\Dto\DbMigrationDto;
use Asterios\Core\Env;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Interfaces\MigrationInterface;
use Asterios\Core\Logger;

class Migration implements MigrationInterface
{
    protected array $errors = [];
    protected string $envFile = '.env';
    protected ?Env $env;

    public function __construct(string $envFile = '.env')
    {
        $this->envFile = $envFile;
        $this->env = new Env($this->envFile);
    }

    /**
     * @inheritDoc
     */
    public function migrate(): bool
    {
        $migrationPath = $this->getMigrationsPath();

        if (!$migrationPath || !is_dir($migrationPath))
        {
            $this->logError('Could not load migration path: ' . $migrationPath);

            return false;
        }

        $files = glob($migrationPath . '/*.php');
        sort($files);

        foreach ($files as $file)
        {
            try
            {
                $migration = require $file;
                if (method_exists($migration, 'up'))
                {
                    $migration->up();
                    Logger::forge()
                        ->info('Run migration: ' . basename($file));
                }
                else
                {
                    throw new \RuntimeException('Missing method "up" in migration:' . basename($file));
                }
            } catch (\Throwable $e)
            {
                $this->logError('Migration failed: ' . basename($file) . ' - ' . $e->getMessage());

                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function rollback(): bool
    {
        $migrationPath = $this->getMigrationsPath();

        if (!$migrationPath || !is_dir($migrationPath))
        {
            $this->logError('Could not load migration path: ' . $migrationPath);

            return false;
        }

        $files = glob($migrationPath . '/*.php');
        rsort($files);

        foreach ($files as $file)
        {
            try
            {
                $migration = require $file;
                if (method_exists($migration, 'down'))
                {
                    $migration->down();
                    Logger::forge()
                        ->info('Rollback migration: ' . basename($file));
                }
                else
                {
                    throw new \RuntimeException('Missing method "down" fehlt in Migration: ' . basename($file));
                }
            } catch (\Throwable $e)
            {
                $this->logError('Rollback failed: ' . basename($file) . ' ' . $e->getMessage());

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
            $this->logError('Could not load seeder path.');

            return false;
        }

        $files = $this->getSeederFilesInOrder($dto->getSeeder(), $seederPath);

        foreach ($files as $file)
        {
            try
            {
                $table = pathinfo($file, PATHINFO_FILENAME);

                Logger::forge()
                    ->info("Seeding table >>> $table");
                Logger::forge()
                    ->info("Seeding file >>> $file");
                Db::write("SET FOREIGN_KEY_CHECKS = 0;");
                //Db::write("DELETE FROM `$table`;");

                Db::forge()
                    ->seedFromFile($file);

                Db::write("SET FOREIGN_KEY_CHECKS = 1;");

                Logger::forge()
                    ->info("Seeded: $table");
                usleep(1000);
            } catch (\JsonException|ConfigLoadException $e)
            {
                $this->logError("Error Seeder for $file: " . $e->getMessage());

                return false;
            }
        }

        return true;
    }

    public function getSeederFilesInOrder(array $seederFiles, string $seederPath): array
    {
        $files = scandir($seederPath);

        $validFiles = array_filter($files, static function ($file) use ($seederPath) {
            return $file !== '.' && $file !== '..' && is_file($seederPath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'json';
        });

        $orderedFiles = $this->sortFilesByDependencies($validFiles, $seederFiles);

        return array_map(static fn($file) => $seederPath . '/' . $file, $orderedFiles);
    }

    /**
     * Sortiert die Seeder-Dateien basierend auf ihren Abhängigkeiten.
     *
     * @param array $files
     * @param array $dependencies
     * @return array
     */
    private function sortFilesByDependencies(array $files, array $dependencies): array
    {
        $sorted = [];
        $visited = [];

        $visit = static function ($file) use ($dependencies, &$visited, &$sorted, &$visit) {
            if (isset($visited[$file]))
            {
                return;
            }

            $visited[$file] = true;

            if (isset($dependencies[$file]))
            {
                foreach ($dependencies[$file] as $dependency)
                {
                    if (!isset($visited[$dependency]))
                    {
                        $visit($dependency);
                    }
                }
            }

            $sorted[] = $file;
        };

        foreach ($files as $file)
        {
            $visit($file);
        }

        return $sorted;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function logError(string $msg): void
    {
        Logger::forge()
            ->error($msg);
        $this->errors[] = $msg;
    }

    protected function getPathsFromEnv(string $key): ?string
    {
        try
        {
            return $this->env->get($key);
        } catch (EnvException|EnvLoadException $e)
        {
            $this->logError("Env-Fehler [$key]: " . $e->getMessage());

            return null;
        }
    }

    protected function getMigrationsPath(): ?string
    {
        $migrationPath = $this->getPathsFromEnv('DATABASE_MIGRATION_PATH');

        return $migrationPath ? $this->getProtectedPath() . $migrationPath : null;
    }

    protected function getSeederPath(): ?string
    {
        $seederPath = $this->getPathsFromEnv('DATABASE_SEEDER_PATH');

        return $seederPath ? $this->getProtectedPath() . $seederPath : null;
    }

    protected function getProtectedPath(): string
    {
        return str_replace('/public', '', Asterios::getDocumentRoot());
    }
}
