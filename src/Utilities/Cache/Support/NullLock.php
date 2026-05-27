<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Support;

use Asterios\Core\Contracts\Utilities\Cache\LockInterface;

final class NullLock implements LockInterface
{
    /**
     * @inheritDoc
     */
    public function acquire(int $ttl = 10): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function release(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isAcquired(): bool
    {
        return true;
    }
}