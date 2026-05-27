<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Drivers;

use Asterios\Core\Contracts\Utilities\Cache\LockInterface;
use Asterios\Core\Contracts\Utilities\Cache\SerializerInterface;
use Asterios\Core\Exception\Utilities\Cache\CacheException;
use Asterios\Core\Utilities\Cache\Support\CachePayload;
use Asterios\Core\Utilities\Cache\Support\FileLock;

class FileDriver extends AbstractDriver
{
    public function __construct(
        private readonly string $cachePath,
        SerializerInterface $serializer,
        string $prefix = 'asterios:',
    ) {
        parent::__construct($serializer, $prefix);
    }

    public function get(string $key): mixed
    {
        $path = $this->pathFor($key);

        if (!is_file($path))
        {
            return null;
        }

        $payload = $this->readPayload($path);

        if ($payload === null)
        {
            return null;
        }

        if ($payload->isExpired())
        {
            @unlink($path);
            return null;
        }

        return $payload->value;
    }

    public function set(
        string $key,
        mixed $value,
        ?int $ttl = null
    ): bool {
        $path = $this->pathFor($key);
        $dir = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir))
        {
            throw new CacheException(sprintf('Directory "%s" was not created', $dir));
        }

        $payload = new CachePayload(
            value: $value,
            expiresAt: $ttl !== null ? time() + $ttl : null,
        );

        $tmp = $path . '.tmp';

        $written = file_put_contents(
            $tmp,
            $this->serializer->serialize($payload->toArray()),
            LOCK_EX
        );

        if ($written === false)
        {
            return false;
        }

        return rename($tmp, $path);
    }

    public function delete(string $key): bool
    {
        $path = $this->pathFor($key);

        if (!is_file($path))
        {
            return true;
        }

        return unlink($path);
    }

    public function clear(): bool
    {
        if (!is_dir($this->cachePath))
        {
            return true;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->cachePath,
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file)
        {
            if ($file->isDir())
            {
                rmdir($file->getPathname());
            }
            else
            {
                unlink($file->getPathname());
            }
        }

        return true;
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

            $this->set($key, $new);

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
        $lock = new FileLock(
            $this->cachePath . '/locks/' . sha1($name) . '.lock'
        );

        $lock->acquire($ttl);

        return $lock;
    }

    public function isAvailable(): bool
    {
        if (!is_dir($this->cachePath))
        {
            return is_writable(dirname($this->cachePath));
        }

        return is_writable($this->cachePath);
    }

    private function pathFor(string $key): string
    {
        $hash = sha1($this->namespacedKey($key));

        return sprintf(
            '%s/%s/%s/%s.cache',
            rtrim($this->cachePath, '/'),
            substr($hash, 0, 2),
            substr($hash, 2, 2),
            $hash
        );
    }

    private function readPayload(string $path): ?CachePayload
    {
        $contents = file_get_contents($path);

        if ($contents === false)
        {
            return null;
        }

        $data = $this->serializer->unserialize($contents);

        if (!is_array($data))
        {
            return null;
        }

        return CachePayload::fromArray($data);
    }
}