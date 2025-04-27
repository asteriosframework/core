<?php declare(strict_types=1);

namespace Asterios\Core\Db;

use Asterios\Core\Asterios;
use Asterios\Core\Config;
use Asterios\Core\Db;
use Asterios\Core\Env;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Exception\MigrationException;
use Asterios\Core\Interfaces\MigrationInterface;
use Asterios\Core\Logger;

class Migration implements MigrationInterface
{
    protected array $errors = [];
    protected array $messages = [];
    protected string $envFile = '.env';

    /**
     * @var array<string, array<int, string>>
     */
    protected array $seeder = [];

    protected ?Env $env = null;

    public function __construct(string $envFile = '.env')
    {
        $this->envFile = Asterios::getBasePath() . DIRECTORY_SEPARATOR . $envFile;
        Config::set_config_path(Asterios::getBasePath() . DIRECTORY_SEPARATOR . 'config');

        if (null === $this->env)
        {
            $this->env = new Env($this->envFile);
        }

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

        $this->ensureMigrationTableExists();
        $files = glob($migrationPath . '/*.php');
        sort($files);
        $batch = $this->getNextBatchNumber();

        foreach ($files as $file)
        {
            $migrationName = basename($file, '.php');

            if ($this->hasMigrationRun($migrationName))
            {
                Logger::forge()
                    ->info('Skipping already run migration: ' . $migrationName);
                $this->messages[][$migrationName] = 'skipped';
                continue;
            }

            try
            {
                $migration = require $file;
                if (method_exists($migration, 'up'))
                {
                    $migration->up();
                    $this->markMigrationAsRun($migrationName, $batch);
                    Logger::forge()
                        ->info('Run migration: ' . basename($file));

                    $this->messages[][$migrationName] = 'done';
                }
                else
                {
                    $this->messages[][$migrationName] = 'missing';
                    throw new \RuntimeException('Missing method "up" in migration:' . basename($file));
                }
            } catch (\Throwable $e)
            {
                $this->messages[][$migrationName] = 'failed';
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

    public function getRanMigrations(): array
    {
        try
        {
            $migrations = Db::read('SELECT migration FROM migration');
        } catch (ConfigLoadException)
        {
            return [];
        }

        if (is_array($migrations))
        {
            return $migrations;
        }

        return [];
    }

    public function getAllMigrationFiles(): array
    {
        $migrationPath = $this->getMigrationsPath();
        $files = glob($migrationPath . '/*.php');

        return $files ?: [];
    }

    /**
     * @inheritDoc
     */
    public function seed(bool $truncateTables = true): bool
    {
        $seederPath = $this->getSeederPath();

        if (null === $seederPath)
        {
            $this->logError('Could not load seeder path.');

            return false;
        }

        if (empty($this->getSeeder()))
        {
            try
            {
                $this->getSeederFromConfig();
            } catch (ConfigLoadException|MigrationException $e)
            {
                $this->logError('Error loading seeder config file: ' . $e->getMessage());

                return false;
            }
        }

        $files = $this->getSeederFilesInOrder($this->getSeeder(), $seederPath);

        foreach ($files as $file)
        {
            try
            {
                $table = pathinfo($file, PATHINFO_FILENAME);

                Db::write('SET FOREIGN_KEY_CHECKS = 0;');

                if ($truncateTables)
                {
                    Db::write("TRUNCATE `$table`;");
                }

                Db::forge()
                    ->seedFromFile($file);

                Db::write('SET FOREIGN_KEY_CHECKS = 1;');

                Logger::forge()
                    ->info('Seeded: ' . $table . '.json');
                usleep(1000);
            } catch (\JsonException|ConfigLoadException $e)
            {
                $this->logError('Error Seeder for ' . $file . ':' . $e->getMessage());

                return false;
            }
        }

        return true;
    }

    /**
     * @param array $seederFiles
     * @param string $seederPath
     * @return array
     */
    private function getSeederFilesInOrder(array $seederFiles, string $seederPath): array
    {
        $files = scandir($seederPath);

        $validFiles = array_filter($files, static function ($file) use ($seederPath) {
            return $file !== '.' && $file !== '..' && is_file($seederPath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'json';
        });

        $orderedFiles = $this->sortFilesByDependencies($validFiles, $seederFiles);

        return array_map(static fn($file) => $seederPath . '/' . $file, $orderedFiles);
    }

    private function sortFilesByDependencies(array $files, array $dependencies): array
    {
        $sorted = [];
        $visited = [];

        $visit = static function ($file) use ($dependencies, &$visited, &$sorted, &$visit) {
            if (isset($visited[$file]))
                return;

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
        return Asterios::getBasePath();
    }

    protected function ensureMigrationTableExists(): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `migration` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL UNIQUE,
    `batch` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;
        Db::write($sql);
    }

    protected function hasMigrationRun(string $migrationName): bool
    {
        $result = Db::read("SELECT COUNT(*) AS count FROM `migration` WHERE `migration` = '" . Db::escape($migrationName) . "'");

        return $result && (int)$result[0]['count'] > 0;
    }

    protected function markMigrationAsRun(string $migrationName, int $batch): void
    {
        $escapedName = Db::escape($migrationName);
        Db::write("INSERT INTO `migration` (`migration`, `batch`) VALUES ('$escapedName', $batch)");
    }

    protected function getNextBatchNumber(): int
    {
        $result = Db::read("SELECT MAX(`batch`) AS max_batch FROM `migration`");

        return isset($result[0]['max_batch']) ? (int)$result[0]['max_batch'] + 1 : 1;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param array<string, array<int, string>> $seeder
     * @return void
     */
    public function setSeeder(array $seeder): void
    {
        $this->seeder = $seeder;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function getSeeder(): array
    {
        return $this->seeder;
    }

    /**
     * @return void
     * @throws MigrationException|ConfigLoadException
     */
    protected function getSeederFromConfig(): void
    {
        $seeder = (array)Config::get('seeder');

        if (empty($seeder))
        {
            throw new MigrationException('Seeder config file is empty!');
        }

        $this->setSeeder($seeder);
    }
}
