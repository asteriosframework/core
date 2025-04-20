<?php declare(strict_types=1);

namespace Asterios\Core\Db;

use Asterios\Core\Asterios;
use Asterios\Core\Db;
use Asterios\Core\Env;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Interfaces\MigrationInterface;
use Asterios\Core\Logger;
use Throwable;

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

    public function seed(array $dependencySeederOrder): bool
    {
        $seederPath = $this->getSeederPath();

        if (null === $seederPath || !is_dir($seederPath))
        {
            $this->logError('Seeder path not found: ' . $seederPath);

            return false;
        }

        $files = $this->getSeederFilesInOrder($dependencySeederOrder, $seederPath);

        foreach ($files as $file)
        {
            if (!file_exists($file))
            {
                Logger::forge()
                    ->warning("Seeder file not found: $file");
                continue;
            }

            $table = basename($file, '.json');
            $json = file_get_contents($file);
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($data) || empty($data))
            {
                Logger::forge()
                    ->warning("No data to seed in file: $file");
                continue;
            }

            try
            {
                Db::write("TRUNCATE TABLE `$table`");

                foreach ($data as $row)
                {
                    $columns = implode('`, `', array_keys($row));
                    $placeholders = implode(', ', array_fill(0, count($row), '?'));
                    $values = array_values($row);

                    $sql = "INSERT INTO `$table` (`$columns`) VALUES ($placeholders)";
                    Db::write($sql, $values);
                }

                Logger::forge()
                    ->info("Seeded: $table");
            } catch (Throwable $e)
            {
                $this->logError("Error seeding table $table: " . $e->getMessage());

                return false;
            }
        }

        return true;
    }

    public function getSeederFilesInOrder(array $dependencySeederOrder, string $seederPath): array
    {
        $orderedFiles = [];
        $seen = [];

        // Die Dateien rekursiv sortieren, um Abhängigkeiten zu berücksichtigen
        $this->resolveDependencies($dependencySeederOrder, $orderedFiles, $seen);

        // Dateipfade erstellen und zurückgeben
        return array_map(static fn($file) => $seederPath . '/' . $file, $orderedFiles);
    }

    private function resolveDependencies(array $dependencySeederOrder, array &$orderedFiles, array &$seen, $currentFile = null): void
    {
        if ($currentFile && isset($seen[$currentFile]))
        {
            return;
        }

        $seen[$currentFile] = true;

        foreach ($dependencyOrder[$currentFile] ?? [] as $dependency)
        {
            $this->resolveDependencies($dependencySeederOrder, $orderedFiles, $seen, $dependency);
        }

        $orderedFiles[] = $currentFile;
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
