<?php declare(strict_types=1);

namespace Asterios\Core\Utilities\Cache\Support;

use Asterios\Core\Contracts\Utilities\Cache\CacheDriverInterface;

final readonly class TagSet
{
    public function __construct(
        private CacheDriverInterface $driver,
        private array $tags,
    ) {
    }

    /**
     * @return array
     */
    public function versions(): array
    {
        $versions = [];

        foreach ($this->tags as $tag)
        {
            $versions[$tag] = $this->driver->getTagVersion($tag);
        }

        return $versions;
    }

    /**
     * @param array $storedVersions
     * @return bool
     */
    public function isValid(array $storedVersions): bool
    {
        foreach ($this->tags as $tag)
        {
            $current = $this->driver->getTagVersion($tag);
            $stored = $storedVersions[$tag] ?? null;

            if ($stored !== $current)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function flush(): bool
    {
        $ok = true;

        foreach ($this->tags as $tag)
        {
            $ok = $ok && ($this->driver->incrementTagVersion($tag) > 0);
        }

        return $ok;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->tags;
    }
}