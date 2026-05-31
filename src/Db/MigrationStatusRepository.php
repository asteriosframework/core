<?php declare(strict_types=1);

namespace Asterios\Core\Db;

use Asterios\Core\Db;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\MigrationException;

final class MigrationStatusRepository
{
    /**
     * @throws ConfigLoadException
     */
    public function ensureTableExists(): void
    {
        $sql = "
CREATE TABLE IF NOT EXISTS `migration` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL UNIQUE,
    `batch` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

        Db::write($sql);
    }

    /**
     * @throws MigrationException
     */
    public function hasRun(string $migrationName): bool
    {
        try
        {
            $result = Db::read(
                "SELECT COUNT(*) AS count
                 FROM `migration`
                 WHERE `migration` = '" . Db::escape($migrationName) . "'"
            );
        }
        catch (ConfigLoadException $e)
        {
            throw new MigrationException(
                'Failed to read migration status: ' . $e->getMessage()
            );
        }

        return $result
            && (int)$result[0]['count'] > 0;
    }

    /**
     * @throws ConfigLoadException
     */
    public function markAsRun(
        string $migrationName,
        int $batch
    ): void {
        $escapedName = Db::escape($migrationName);

        Db::write(
            "INSERT INTO `migration`
             (`migration`, `batch`)
             VALUES ('$escapedName', $batch)"
        );
    }

    /**
     * @throws ConfigLoadException
     */
    public function getNextBatchNumber(): int
    {
        $result = Db::read(
            'SELECT MAX(`batch`) AS max_batch FROM `migration`'
        );

        return isset($result[0]['max_batch'])
            ? (int)$result[0]['max_batch'] + 1
            : 1;
    }

    /**
     * @throws ConfigLoadException
     */
    public function getRanMigrations(): array
    {
        $result =  Db::read(
            'SELECT migration FROM migration'
        );

        return is_array($result)
            ? $result
            : [];
    }
}