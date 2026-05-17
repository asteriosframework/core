<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Config\SessionConfig;
use Asterios\Core\Contracts\Session\SessionInterface;
use Asterios\Core\Contracts\SessionFacadeInterface;
use Asterios\Core\Session\FlashBag;
use Asterios\Core\Session\SessionManager;
use Asterios\Core\Session\Store\ExpiringStore;
use Asterios\Core\Session\Store\PhpSessionStore;

class Session implements SessionFacadeInterface
{
    public const string USER_SESSION_KEY = 'user';

    private static ?SessionInterface $instance = null;

    /**
     * @inheritDoc
     */
    public static function forge(): self
    {
        static::instance()->start();

        return new self();
    }

    /**
     * @inheritDoc
     */
    public static function setInstance(SessionInterface $session): void
    {
        self::$instance = $session;
    }

    /**
     * @inheritDoc
     */
    public static function instance(): SessionInterface
    {
        if (self::$instance === null)
        {
            self::$instance = new SessionManager(
                new PhpSessionStore(),
                new SessionConfig(namespace: self::USER_SESSION_KEY),
                new FlashBag(),
                new ExpiringStore(),
            );
        }

        return self::$instance;
    }

    /**
     * @inheritDoc
     */
    public static function exists(): bool
    {
        return self::instance()->exists();
    }

    /**
     * @inheritDoc
     */
    public static function isStarted(): bool
    {
        return self::instance()->isStarted();
    }

    /**
     * @inheritDoc
     */
    public static function get(string|array|null $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null
    {
        return self::instance()->get($key, $default);
    }

    /**
     * @inheritDoc
     */
    public static function set(string|array|null $key, array|string|int|float|bool|null $value = null): void
    {
        self::instance()->set($key, $value);
    }

    /**
     * @inheritDoc
     */
    public static function has(string $key): bool
    {
        return self::instance()->has($key);
    }

    /**
     * @inheritDoc
     */
    public static function remove(string $key): void
    {
        self::instance()->remove($key);
    }

    /**
     * @inheritDoc
     */
    public static function clear(): void
    {
        self::instance()->clear();
    }

    /**
     * @inheritDoc
     */
    public static function pull(string $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null
    {
        return self::instance()->pull($key, $default);
    }

    /**
     * @inheritDoc
     */
    public static function regenerate(bool $destroy = true): bool
    {
        return self::instance()->regenerate($destroy);
    }

    /**
     * @inheritDoc
     */
    public static function destroy(): void
    {
        self::instance()->destroy();
    }

    /**
     * @inheritDoc
     */
    public static function invalidate(): void
    {
        self::instance()->invalidate();
    }

    /**
     * @inheritDoc
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * @inheritDoc
     */
    public static function getString(string $key, ?string $default = null): ?string
    {
        return self::instance()->getString($key, $default);
    }

    /**
     * @inheritDoc
     */
    public static function getInt(string $key, ?int $default = null): ?int
    {
        return self::instance()->getInt($key, $default);
    }

    /**
     * @inheritDoc
     */
    public static function getFloat(string $key, ?float $default = null): ?float
    {
        return self::instance()->getFloat($key, $default);
    }

    /**
     * @inheritDoc
     */
    public static function getBool(string $key, ?bool $default = null): ?bool
    {
        return self::instance()->getBool($key, $default);
    }

    /**
     * @inheritDoc
     */
    public static function getArray(string $key, array $default = []): array
    {
        return self::instance()->getArray($key, $default);
    }

    /**
     * @inheritDoc
     */
    public static function flash(string $key, array|string|int|float|bool|null $value): void
    {
        self::instance()->flash($key, $value);
    }

    /**
     * @inheritDoc
     */
    public static function getFlash(string $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null
    {
        return self::instance()->getFlash($key, $default);
    }

    /**
     * @inheritDoc
     */
    public static function hasFlash(string $key): bool
    {
        return self::instance()->hasFlash($key);
    }

    /**
     * @inheritDoc
     */
    public static function keepFlash(string|array $keys): void
    {
        self::instance()->keepFlash($keys);
    }

    /**
     * @inheritDoc
     */
    public static function reflash(): void
    {
        self::instance()->reflash();
    }

    /**
     * @inheritDoc
     */
    public static function clearFlash(): void
    {
        self::instance()->clearFlash();
    }

    /**
     * @inheritDoc
     */
    public static function putWithTtl(string $key, array|string|int|float|bool|null $value, int $ttlSeconds): void
    {
        self::instance()->putWithTtl($key, $value, $ttlSeconds);
    }

    /**
     * @inheritDoc
     */
    public static function hasExpired(string $key): bool
    {
        return self::instance()->hasExpired($key);
    }

    /**
     * @inheritDoc
     */
    public static function purgeExpired(): void
    {
        self::instance()->purgeExpired();
    }
}
