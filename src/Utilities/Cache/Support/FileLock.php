<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Support;

use Asterios\Core\Contracts\Utilities\Cache\LockInterface;
use Asterios\Core\Exception\Utilities\Cache\LockException;

final class FileLock implements LockInterface
{
    private mixed $handle = null;
    private bool $acquired = false;

    public function __construct(
        private readonly string $path,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function acquire(int $ttl = 10): bool
    {
        $dir = dirname($this->path);

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir))
        {
            throw new LockException(sprintf('Directory "%s" was not created', $dir));
        }

        $this->handle = fopen($this->path, 'c+');

        if ($this->handle === false)
        {
            return false;
        }

        $this->acquired = flock($this->handle, LOCK_EX | LOCK_NB);

        return $this->acquired;
    }

    /**
     * @inheritDoc
     */
    public function release(): bool
    {
        if (!$this->acquired || $this->handle === null)
        {
            return false;
        }

        flock($this->handle, LOCK_UN);
        fclose($this->handle);

        $this->handle = null;
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