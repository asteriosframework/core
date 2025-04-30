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

    protected ?Env $env = null;

    protected bool $forceMigration = false;

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

        try
        {
            $this->ensureMigrationTableExists();
            $batch = $this->getNextBatchNumber();
        } catch (ConfigLoadException $e)
        {
            $this->logError('Could not load config file:' . $e->getMessage());

            return false;
        }

        $files = glob($migrationPath . '/*.php');
        sort($files);

        foreach ($files as $file)
        {
            try
            {
                $migrationName = basename($file, '.php');

                if (!$this->forceMigration && $this->hasMigrationRun($migrationName))
                {
                    Logger::forge()
                        ->info('Skipping already run migration: ' . $migrationName);
                    $this->messages[][$migrationName] = 'skipped';
                    continue;
                }

                if ($this->forceMigration)
                {
                    $tableName = $this->getTableName($migrationName);

                    Logger::forge()
                        ->info('Drop table ' . $migrationName);

                    $this->dropTable($tableName);
                }

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
        $this->ensureMigrationTableExists();

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

    public function hasMigrated(array $migrationsArray, string $migrationName): bool
    {
        foreach ($migrationsArray as $migration)
        {
            if (($migration['migration'] ?? '') === $migrationName)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritDoc
     */
    public function getMigrationsPath(): ?string
    {
        $migrationPath = $this->getPathsFromEnv('DATABASE_MIGRATION_PATH');

        return $migrationPath ? $this->getProtectedPath() . $migrationPath : null;
    }

    /**
     * @inheritDoc
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @inheritDoc
     */
    public function force(): self
    {
        $this->forceMigration = true;

        return $this;
    }

    /**
     * @param string $msg
     * @return void
     */
    protected function logError(string $msg): void
    {
        Logger::forge()
            ->error($msg);
        $this->errors[] = $msg;
    }

    /**
     * @param string $key
     * @return string|null
     */
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

    /**
     * @return string
     */
    protected function getProtectedPath(): string
    {
        return Asterios::getBasePath();
    }

    /**
     * @return void
     * @throws ConfigLoadException
     */
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

    /**
     * @param string $migrationName
     * @return bool
     * @throws MigrationException
     */
    private function hasMigrationRun(string $migrationName): bool
    {
        try
        {
            $result = Db::read("SELECT COUNT(*) AS count FROM `migration` WHERE `migration` = '" . Db::escape($migrationName) . "'");
        } catch (ConfigLoadException $e)
        {
            throw new MigrationException('Failed to load config for reading migration status in database: ' . $e->getMessage());
        }

        return $result && (int)$result[0]['count'] > 0;
    }

    /**
     * @param string $migrationName
     * @param int $batch
     * @return void
     * @throws ConfigLoadException
     */
    protected function markMigrationAsRun(string $migrationName, int $batch): void
    {
        $escapedName = Db::escape($migrationName);
        Db::write("INSERT INTO `migration` (`migration`, `batch`) VALUES ('$escapedName', $batch)");
    }

    /**
     * @return int
     * @throws ConfigLoadException
     */
    private function getNextBatchNumber(): int
    {
        $result = Db::read('SELECT MAX(`batch`) AS max_batch FROM `migration`');

        return isset($result[0]['max_batch']) ? (int)$result[0]['max_batch'] + 1 : 1;
    }

    /**
     * @param string $tableName
     * @return void
     * @throws ConfigLoadException
     */
    private function DropTable(string $tableName): void
    {
        Db::read('DROP TABLE IF EXISTS `' . $tableName . '`');
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
