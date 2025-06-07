<?php

namespace Asterios\Core;

use ArrayAccess;

class Session
{
    public const USER_SESSION_KEY = 'user';

    /**
     * Creates a new Session instance.
     *
     * @return  Session The new session object
     */
    public static function forge(): Session
    {
        return new self();
    }

    /**
     * @return bool
     */
    public static function exists(): bool
    {
        return isset($_SESSION['user']);
    }

    /**
     * This function get given session value.
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(mixed $key, $default = null): mixed
    {
        $array = $_SESSION[self::USER_SESSION_KEY] ?? [];

        if (is_null($key))
        {
            return $array;
        }

        if (is_array($key))
        {
            $return = [];

            foreach ($key as $k)
            {
                $return[$k] = static::get($k, $default);
            }

            return $return;
        }

        is_object($key) && ($key = (string)$key);

        if (array_key_exists($key, $array))
        {
            return $array[$key];
        }

        foreach (explode('.', $key) as $key_part)
        {
            if (!($array instanceof ArrayAccess && isset($array[$key_part])))
            {
                if (!is_array($array) || !array_key_exists($key_part, $array))
                {
                    return $default;
                }
            }

            $array = $array[$key_part];
        }

        return $array;
    }

    public static function set($key, $value): void
    {
        static::set_helper($_SESSION[self::USER_SESSION_KEY], $key, $value);
    }

    /**
     * @param string $key
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[self::USER_SESSION_KEY][$key]);
    }

    public static function destroy(): void
    {
        session_destroy();
    }

    protected static function set_helper(&$array, $key, $value = null): void
    {
        if (is_null($key))
        {
            $array = $value;

            return;
        }
        if (is_array($key))
        {
            foreach ($key as $k => $v)
            {
                static::set_helper($array, $k, $v);
            }
        }
        else
        {
            $keys = explode('.', $key);
            while (count($keys) > 1)
            {
                $key = array_shift($keys);
                if (!isset($array[$key]) || !is_array($array[$key]))
                {
                    $array[$key] = [];
                }
                $array = &$array[$key];
            }
            $array[array_shift($keys)] = $value;
        }
    }
}
