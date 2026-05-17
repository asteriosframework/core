<?php declare(strict_types=1);

namespace Asterios\Core\Session\Store;

use Asterios\Core\Contracts\Session\Store\ExpiringStoreInterface;
use Asterios\Core\Session\Support\Arr;

final class ExpiringStore implements ExpiringStoreInterface
{
    private const string META_KEY = '__asterios';
    private const string TTL_KEY = 'ttl';

    /**
     * @inheritDoc
     */
    public function initialize(array &$session): void
    {
        $session[self::META_KEY] ??= [];
        $session[self::META_KEY][self::TTL_KEY] ??= [];
    }

    /**
     * @inheritDoc
     */
    public function put(array &$session, string $key, array|string|int|float|bool|null $value, int $ttlSeconds): void
    {
        $this->initialize($session);

        Arr::set($session, $key, $value);
        $session[self::META_KEY][self::TTL_KEY][$key] = time() + $ttlSeconds;
    }

    /**
     * @inheritDoc
     */
    public function hasExpired(array &$session, string $key): bool
    {
        $this->initialize($session);

        if (!array_key_exists($key, $session[self::META_KEY][self::TTL_KEY]))
        {
            return false;
        }

        return $session[self::META_KEY][self::TTL_KEY][$key] <= time();
    }

    /**
     * @inheritDoc
     */
    public function purge(array &$session): void
    {
        $this->initialize($session);

        foreach ($session[self::META_KEY][self::TTL_KEY] as $key => $expiresAt)
        {
            if ($expiresAt > time())
            {
                continue;
            }

            Arr::forget($session, $key);
            unset($session[self::META_KEY][self::TTL_KEY][$key]);
        }
    }

    /**
     * @inheritDoc
     */
    public function clear(array &$session): void
    {
        $this->initialize($session);
        $session[self::META_KEY][self::TTL_KEY] = [];
    }
}
