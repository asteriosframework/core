<?php

declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache;

use DateInterval;
use Psr\SimpleCache\CacheInterface;

final readonly class Psr16CacheAdapter implements CacheInterface
{
    public function __construct(
        private Cache $cache,
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($key, $default);
    }

    public function set(
        string $key,
        mixed $value,
        null|int|DateInterval $ttl = null
    ): bool {
        return $this->cache->set(
            $key,
            $value,
            $this->normalizeTtl($ttl)
        );
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function getMultiple(
        iterable $keys,
        mixed $default = null
    ): iterable {
        $items = $this->cache->getMultiple($keys);

        foreach ($items as &$item)
        {
            if ($item === null)
            {
                $item = $default;
            }
        }

        return $items;
    }

    public function setMultiple(
        iterable $values,
        null|int|DateInterval $ttl = null
    ): bool {
        return $this->cache->setMultiple(
            $values,
            $this->normalizeTtl($ttl)
        );
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return $this->cache->deleteMultiple($keys);
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    private function normalizeTtl(
        null|int|DateInterval $ttl
    ): ?int {
        if ($ttl === null)
        {
            return null;
        }

        if (is_int($ttl))
        {
            return $ttl;
        }

        $now = new \DateTimeImmutable();

        return $now->add($ttl)->getTimestamp() - $now->getTimestamp();
    }
}
