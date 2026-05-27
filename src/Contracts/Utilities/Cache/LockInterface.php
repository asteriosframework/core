<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Utilities\Cache;

interface LockInterface {

    /**
     * @param int $ttl
     * @return bool
     */
    public function acquire(int $ttl = 10): bool;

    /**
     * @return bool
     */
    public function release(): bool;

    /**
     * @return bool
     */
    public function isAcquired(): bool;
}