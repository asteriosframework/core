<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Drivers;

use Asterios\Core\Contracts\Utilities\Cache\LockInterface;
use Asterios\Core\Contracts\Utilities\Cache\SerializerInterface;
use Asterios\Core\Db;
use Asterios\Core\Utilities\Cache\Support\CachePayload;
use Asterios\Core\Utilities\Cache\Support\MySqlLock;
use Throwable;

class MysqlDriver extends AbstractDriver
{
    public function __construct(
        private readonly string $configGroup = 'default',
        private readonly string $table = 'cache_entries',
        SerializerInterface $serializer,
        string $prefix = 'asterios:',
    ) {
        parent::__construct($serializer, $prefix);
    }

    public function get(string $key): mixed
    {
        $this->runGarbageCollection();

        $sql = sprintf(
            'SELECT cache_value, expires_at
             FROM `%s`
             WHERE cache_key = %s
             LIMIT 1',
            $this->table,
            Db::quote($this->namespacedKey($key), $this->configGroup)
        );

        $rows = Db::read($sql, $this->configGroup);

        if ($rows === false || empty($rows[0]))
        {
            return null;
        }

        $row = $rows[0];

        $expiresAt = $row['expires_at'] !== null
            ? (int)$row['expires_at']
            : null;

        if ($expiresAt !== null && $expiresAt <= time())
        {
            $this->delete($key);
            return null;
        }

        $payload = $this->serializer->unserialize(
            $row['cache_value']
        );

        if (!is_array($payload))
        {
            return null;
        }

        return CachePayload::fromArray($payload)->value;
    }

    public function set(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        $now = time();

        $payload = new CachePayload(
            value: $value,
            expiresAt: $ttl !== null ? $now + $ttl : null,
        );

        $serialized = $this->serializer->serialize(
            $payload->toArray()
        );

        $sql = sprintf(
            'INSERT INTO `%s`
                (cache_key, cache_value, expires_at, created_at, updated_at)
             VALUES (%s, %s, %s, %d, %d)
             ON DUPLICATE KEY UPDATE
                cache_value = VALUES(cache_value),
                expires_at = VALUES(expires_at),
                updated_at = VALUES(updated_at)',
            $this->table,
            Db::quote($this->namespacedKey($key), $this->configGroup),
            Db::quote($serialized, $this->configGroup),
            $payload->expiresAt !== null
                ? Db::quote($payload->expiresAt, $this->configGroup)
                : 'NULL',
            $now,
            $now
        );

        return Db::write($sql, $this->configGroup);
    }

    public function delete(string $key): bool
    {
        $sql = sprintf(
            'DELETE FROM `%s`
             WHERE cache_key = %s',
            $this->table,
            Db::quote($this->namespacedKey($key), $this->configGroup)
        );

        return Db::write($sql, $this->configGroup);
    }

    public function clear(): bool
    {
        $sql = sprintf(
            'TRUNCATE TABLE `%s`',
            $this->table
        );

        return Db::write($sql, $this->configGroup);
    }

    public function increment(
        string $key,
        int $step = 1
    ): int|false {
        $lock = $this->lock('increment:' . $key, 5);

        if (!$lock->isAcquired())
        {
            return false;
        }

        try
        {
            $current = $this->get($key) ?? 0;

            if (!is_numeric($current))
            {
                return false;
            }

            $new = (int)$current + $step;

            if (!$this->set($key, $new))
            {
                return false;
            }

            return $new;
        }
        finally
        {
            $lock->release();
        }
    }

    public function decrement(
        string $key,
        int $step = 1
    ): int|false {
        return $this->increment($key, -$step);
    }

    public function add(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        if ($this->has($key))
        {
            return false;
        }

        return $this->set($key, $value, $ttl);
    }

    public function lock(
        string $name,
        int $ttl = 10
    ): LockInterface {
        $lock = new MySqlLock(
            $this->lockKey($name),
            $this->configGroup
        );

        $lock->acquire($ttl);

        return $lock;
    }

    public function isAvailable(): bool
    {
        try
        {
            return Db::read('SELECT 1', $this->configGroup) !== false;
        }
        catch (Throwable)
        {
            return false;
        }
    }

    private function runGarbageCollection(): void
    {
        if (random_int(1, 100) !== 1)
        {
            return;
        }

        $sql = sprintf(
            'DELETE FROM `%s`
             WHERE expires_at IS NOT NULL
             AND expires_at <= %d',
            $this->table,
            time()
        );

        Db::write($sql, $this->configGroup);
    }
}
