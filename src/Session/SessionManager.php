<?php declare(strict_types=1);

namespace Asterios\Core\Session;

use Asterios\Core\Config\SessionConfig;
use Asterios\Core\Contracts\Session\SessionInterface;
use Asterios\Core\Contracts\Session\Store\SessionStoreInterface;
use Asterios\Core\Session\Store\ExpiringStore;
use Asterios\Core\Session\Store\PhpSessionStore;
use Asterios\Core\Session\Support\Arr;

final class SessionManager implements SessionInterface
{
    private static ?object $hasSentinel = null;
    private const string META_KEY = '__asterios';
    private readonly FlashBag $flashBag;
    private readonly ExpiringStore $expiringStore;
    private bool $initialized = false;

    public function __construct(
        private readonly SessionStoreInterface $store = new PhpSessionStore(),
        private readonly ?SessionConfig $config = null,
        ?FlashBag $flashBag = null,
        ?ExpiringStore $expiringStore = null,
    ) {
        $this->flashBag = $flashBag ?? new FlashBag();
        $this->expiringStore = $expiringStore ?? new ExpiringStore();
    }

    /**
     * @inheritDoc
     */
    public function start(): void
    {
        if (($this->config?->autoStart ?? true) === true)
        {
            $this->store->start();
        }

        $root = &$this->store->root();
        $root[$this->namespace()] ??= [];

        $this->flashBag->initialize($root[$this->namespace()]);
        $this->expiringStore->initialize($root[$this->namespace()]);

        if ($this->initialized === false)
        {
            $this->flashBag->age($root[$this->namespace()]);
            $this->expiringStore->purge($root[$this->namespace()]);
            $this->initialized = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function isStarted(): bool
    {
        return $this->store->isStarted();
    }

    /**
     * @inheritDoc
     */
    public function exists(): bool
    {
        $root = &$this->store->root();
        return array_key_exists($this->namespace(), $root);
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        $this->start();

        $root = &$this->store->root();
        $data = $root[$this->namespace()];

        unset($data[self::META_KEY]);

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function get(array|string|null $key, float|int|bool|array|string|null $default = null): array|string|int|float|bool|null
    {
        return Arr::get($this->all(), $key, $default);
    }

    /**
     * @inheritDoc
     */
    public function set(array|string|null $key, float|int|bool|array|string|null $value = null): void
    {
        $this->start();
        $root = &$this->store->root();
        Arr::set($root[$this->namespace()], $key, $value);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        $this->start();

        $root = &$this->store->root();

        return Arr::has($root[$this->namespace()], $key);
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): void
    {
        $this->start();
        $root = &$this->store->root();
        Arr::forget($root[$this->namespace()], $key);
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->start();
        $root = &$this->store->root();
        $root[$this->namespace()] = [];
    }

    /**
     * @inheritDoc
     */
    public function pull(string $key, float|int|bool|array|string|null $default = null): array|string|int|float|bool|null
    {
        $value = $this->get($key, $default);
        $this->remove($key);
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function regenerate(bool $destroy = true): bool
    {
        return $this->store->regenerate($destroy);
    }

    public function invalidate(): void
    {
        $this->clear();
        $this->initialized = false;
        $this->regenerate();
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        $this->store->destroy();
    }

    /**
     * @inheritDoc
     */
    public function getString(string $key, ?string $default = null): ?string
    {
        $value = $this->get($key, $default);

        return is_string($value) ? $value : $default;
    }

    /**
     * @inheritDoc
     */
    public function getInt(string $key, ?int $default = null): ?int
    {
        $value = $this->get($key, $default);

        return is_int($value) ? $value : $default;
    }

    /**
     * @inheritDoc
     */
    public function getFloat(string $key, ?float $default = null): ?float
    {
        $value = $this->get($key, $default);

        return is_float($value) ? $value : $default;
    }

    /**
     * @inheritDoc
     */
    public function getBool(string $key, ?bool $default = null): ?bool
    {
        $value = $this->get($key, $default);

        return is_bool($value) ? $value : $default;
    }

    /**
     * @inheritDoc
     */
    public function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key, $default);

        return is_array($value) ? $value : $default;
    }

    /**
     * @inheritDoc
     */
    public function flash(string $key, array|string|int|float|bool|null $value): void
    {
        $this->start();

        $root = &$this->store->root();

        $this->flashBag->flash($root[$this->namespace()], $key, $value);
    }

    /**
     * @inheritDoc
     */
    public function getFlash(string $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null
    {
        $this->start();

        $root = &$this->store->root();

        return $this->flashBag->get(
            $root[$this->namespace()],
            $key,
            $default
        );
    }

    /**
     * @inheritDoc
     */
    public function hasFlash(string $key): bool
    {
        $this->start();

        $root = &$this->store->root();

        return $this->flashBag->has($root[$this->namespace()], $key);
    }

    /**
     * @inheritDoc
     */
    public function keepFlash(string|array $keys): void
    {
        $this->start();

        $root = &$this->store->root();

        $this->flashBag->keep($root[$this->namespace()], $keys);
    }

    /**
     * @inheritDoc
     */
    public function reflash(): void
    {
        $this->start();

        $root = &$this->store->root();

        $this->flashBag->reflash($root[$this->namespace()]);
    }

    /**
     * @inheritDoc
     */
    public function clearFlash(): void
    {
        $this->start();

        $root = &$this->store->root();

        $this->flashBag->clear($root[$this->namespace()]);
    }

    /**
     * @inheritDoc
     */
    public function putWithTtl(string $key, array|string|int|float|bool|null $value, int $ttlSeconds): void
    {
        $this->start();
        $root = &$this->store->root();

        $this->expiringStore->put($root[$this->namespace()], $key, $value, $ttlSeconds);
    }

    /**
     * @inheritDoc
     */
    public function hasExpired(string $key): bool
    {
        $this->start();
        $root = &$this->store->root();

        return $this->expiringStore->hasExpired($root[$this->namespace()], $key);
    }

    /**
     * @inheritDoc
     */
    public function purgeExpired(): void
    {
        $this->start();
        $root = &$this->store->root();

        $this->expiringStore->purge($root[$this->namespace()]);
    }

    /**
     * @return string
     */
    private function namespace(): string
    {
        return $this->config?->namespace ?? 'user';
    }

    private static function sentinel(): object
    {
        self::$hasSentinel ??= new \stdClass();

        return self::$hasSentinel;
    }
}