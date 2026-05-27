<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Support;

use Asterios\Core\Contracts\Utilities\Cache\LockInterface;

final class ApcuLock implements LockInterface
{
    private bool $acquired = false;

    public function __construct(
        private readonly string $key,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function acquire(int $ttl = 10): bool
    {
        $this->acquired = apcu_add($this->key, 1, $ttl);

        return $this->acquired;
    }

    /**
     * @inheritDoc
     */
    public function release(): bool
    {
        if (!$this->acquired)
        {
            return false;
        }

        apcu_delete($this->key);
        $this->acquired = false;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function isAcquired(): bool
    {
        return $this->acquired;
    }

    public function __destruct()
    {
        $this->release();
    }
}