<?php declare(strict_types=1);

namespace Asterios\Core\Session;

use Asterios\Core\Contracts\Session\FlashBagInterface;

final class FlashBag implements FlashBagInterface
{
    private const string META_KEY = '__asterios';
    private const string FLASH_KEY = 'flash';

    /**
     * @param array<string, mixed> $session
     */
    public function initialize(array &$session): void
    {
        $session[self::META_KEY] ??= [];

        $session[self::META_KEY][self::FLASH_KEY] ??= [
            'new' => [],
            'old' => [],
            'data' => [],
        ];
    }

    /**
     * @inheritDoc
     */
    public function flash(array &$session, string $key, array|string|int|float|bool|null $value): void
    {
        $this->initialize($session);

        $flash = &$session[self::META_KEY][self::FLASH_KEY];

        $flash['data'][$key] = $value;

        if (!in_array($key, $flash['new'], true))
        {
            $flash['new'][] = $key;
        }

        $flash['old'] = array_values(
            array_filter(
                $flash['old'],
                static fn (string $item): bool => $item !== $key
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function get(array &$session, string $key, array|string|int|float|bool|null $default = null): array|string|int|float|bool|null
    {
        $this->initialize($session);

        $flash = &$session[self::META_KEY][self::FLASH_KEY];

        if (!array_key_exists($key, $flash['data']))
        {
            return $default;
        }

        return $flash['data'][$key];
    }

    /**
     * @inheritDoc
     */
    public function has(array &$session, string $key): bool
    {
        $this->initialize($session);

        return array_key_exists(
            $key,
            $session[self::META_KEY][self::FLASH_KEY]['data']
        );
    }

    /**
     * @inheritDoc
     */
    public function keep(array &$session, string|array $keys): void
    {
        $this->initialize($session);

        $keys = is_array($keys) ? $keys : [$keys];

        $flash = &$session[self::META_KEY][self::FLASH_KEY];

        foreach ($keys as $key)
        {
            if (!array_key_exists($key, $flash['data']))
            {
                continue;
            }

            if (!in_array($key, $flash['new'], true))
            {
                $flash['new'][] = $key;
            }

            $flash['old'] = array_values(
                array_filter(
                    $flash['old'],
                    static fn (string $item): bool => $item !== $key
                )
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function reflash(array &$session): void
    {
        $this->initialize($session);

        $flash = &$session[self::META_KEY][self::FLASH_KEY];

        foreach ($flash['data'] as $key => $_)
        {
            if (!in_array($key, $flash['new'], true))
            {
                $flash['new'][] = $key;
            }
        }

        $flash['old'] = [];
    }

    /**
     * @inheritDoc
     */
    public function clear(array &$session): void
    {
        $this->initialize($session);

        $session[self::META_KEY][self::FLASH_KEY] = [
            'new' => [],
            'old' => [],
            'data' => [],
        ];
    }

    /**
     * @inheritDoc
     */
    public function age(array &$session): void
    {
        $this->initialize($session);

        $flash = &$session[self::META_KEY][self::FLASH_KEY];

        foreach ($flash['old'] as $key)
        {
            unset($flash['data'][$key]);
        }

        $flash['old'] = $flash['new'];
        $flash['new'] = [];
    }
}
