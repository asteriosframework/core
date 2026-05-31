<?php declare(strict_types=1);

namespace Asterios\Core\Operation;

use Asterios\Core\Db;
use Asterios\Core\Exception\ConfigLoadException;

final class OperationStatusRepository
{
    /**
     * @throws ConfigLoadException
     */
    public function ensureTableExists(): void
    {
        $sql = "
CREATE TABLE IF NOT EXISTS `operation` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `operation` VARCHAR(255) NOT NULL UNIQUE,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

        Db::write($sql);
    }

    /**
     * @throws ConfigLoadException
     */
    public function hasRun(string $operationName): bool
    {
        $result = Db::read(
            "SELECT COUNT(*) AS count
             FROM `operation`
             WHERE `operation` = '" . Db::escape($operationName) . "'"
        );

        return $result
            && (int)$result[0]['count'] > 0;
    }

    /**
     * @throws ConfigLoadException
     */
    public function markAsRun(string $operationName): void
    {
        $escapedName = Db::escape($operationName);

        Db::write(
            "INSERT INTO `operation`
             (`operation`)
             VALUES ('$escapedName')"
        );
    }

    /**
     * @throws ConfigLoadException
     */
    public function getRanOperations(): array
    {
        $result = Db::read(
            'SELECT operation, executed_at FROM operation'
        );

        return is_array($result)
            ? $result
            : [];
    }

    /**
     * @throws ConfigLoadException
     */
    public function ensureMarkedAsRun(
        string $operationName
    ): void {
        if (!$this->hasRun($operationName))
        {
            $this->markAsRun($operationName);
        }
    }
}