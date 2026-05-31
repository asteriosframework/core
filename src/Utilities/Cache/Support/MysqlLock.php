<?php

declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Support;

use Asterios\Core\Contracts\Utilities\Cache\LockInterface;
use Asterios\Core\Db;

final class MySqlLock implements LockInterface
{
    private bool $acquired = false;

    public function __construct(
        private readonly string $name,
        private readonly string $configGroup = 'default',
    ) {
    }

    public function acquire(int $ttl = 10): bool
    {
        $sql = sprintf(
            'SELECT GET_LOCK(%s, %d) AS lock_status',
            Db::quote($this->name, $this->configGroup),
            $ttl
        );

        $rows = Db::read($sql, $this->configGroup);

        $this->acquired = (
            $rows !== false
            && isset($rows[0]['lock_status'])
            && (int)$rows[0]['lock_status'] === 1
        );

        return $this->acquired;
    }

    public function release(): bool
    {
        if (!$this->acquired)
        {
            return false;
        }

        $sql = sprintf(
            'SELECT RELEASE_LOCK(%s)',
            Db::quote($this->name, $this->configGroup)
        );

        Db::read($sql, $this->configGroup);

        $this->acquired = false;

        return true;
    }

    public function isAcquired(): bool
    {
        return $this->acquired;
    }

    public function __destruct()
    {
        $this->release();
    }
}
