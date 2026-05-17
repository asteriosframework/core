<?php declare(strict_types=1);

namespace Asterios\Core\Session\Support;

use Asterios\Core\Contracts\Session\Support\ArrInterface;

class Arr implements ArrInterface{

    /**
     * @inheritDoc
     */
    public static function get(array $source, string|array|null $key, array|string|int|float|bool|null $default = null,): array|string|int|float|bool|null {
        if ($key === null)
        {
            return $source;
        }

        if (is_array($key))
        {
            $result = [];
            foreach ($key as $item)
            {
                $result[$item] = self::get($source, $item, $default);
            }

            return $result;
        }

        if (array_key_exists($key, $source))
        {
            $value = $source[$key];
            return self::normalizeValue($value, $default);
        }

        $current = $source;

        foreach (explode('.', $key) as $segment)
        {
            if (!is_array($current) || !array_key_exists($segment, $current))
            {
                return $default;
            }

            $current = $current[$segment];
        }

        return self::normalizeValue($current, $default);
    }

    /**
     * @inheritDoc
     */
    public static function set(array &$source, string|array|null $key, array|string|int|float|bool|null $value = null,): void
    {
        if ($key === null)
        {
            $source = is_array($value) ? $value : [];
            return;
        }

        if (is_array($key))
        {
            foreach ($key as $itemKey => $itemValue)
            {
                self::set($source, (string) $itemKey, $itemValue);
            }

            return;
        }

        $segments = explode('.', $key);
        $current = &$source;

        while (count($segments) > 1)
        {
            $segment = array_shift($segments);

            if (!isset($current[$segment]) || !is_array($current[$segment]))
            {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }

        $current[array_shift($segments)] = $value;
    }


    /**
     * @inheritDoc
     */
    public static function has(array $source, string $key): bool
    {
        return self::get($source, $key, '__ASTERIOS_SENTINEL__') !== '__ASTERIOS_SENTINEL__';
    }

    /**
     * @inheritDoc
     */
    public static function forget(array &$source, string $key): void {
        $segments = explode('.', $key);
        $current = &$source;

        while (count($segments) > 1)
        {
            $segment = array_shift($segments);

            if (!isset($current[$segment]) || !is_array($current[$segment]))
            {
                return;
            }

            $current = &$current[$segment];
        }

        unset($current[array_shift($segments)]);
    }

    /**
     * @param mixed $value
     * @param array|string|int|float|bool|null $default
     * @return array|string|int|float|bool|null
     */
    private static function normalizeValue(mixed $value, array|string|int|float|bool|null $default): array|string|int|float|bool|null
    {
        if (is_array($value) || is_string($value) || is_int($value) || is_float($value) || is_bool($value) || $value === null)
        {
            return $value;
        }

        return $default;
    }
}