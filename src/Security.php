<?php

declare(strict_types=1);

namespace Asterios\Core;

/**
 * Security class to reduce vulnerabilities
 */
class Security
{
    /**
     * Cleans the global $_GET, $_POST and $_COOKIE arrays
     */
    public static function clean_input(): void
    {
        $_GET = static::clean($_GET);
        $_POST = static::clean($_POST);
        $_COOKIE = static::clean($_COOKIE);
    }

    /**
     * This method generally clean given value
     * @param mixed $value Input value to clean
     * @param string|null $filters Filter; example: xss_clean
     * @param string $type Type of filter: input_filter, output_filter
     * @return mixed
     * @throws Exception\ConfigLoadException
     */
    public static function clean($value, ?string $filters = null, string $type = 'input_filter')
    {
        $_filters = $filters ?? Config::get('default', 'security.' . $type);
        $filter_data = is_array($_filters) ? $_filters : [$_filters];

        foreach ($filter_data as $filter)
        {
            // is this filter a callable local function?
            if (is_string($filter) && is_callable((static::class . '::' . $filter)(...)))
            {
                $value = static::$filter($value);
            }
        }

        return $value;
    }

    /**
     * This method perform xss clean on given value with given htmLawed options
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public static function xss_clean($value, array $options = [])
    {
        if (!is_array($value))
        {
            return \Htmlawed::filter($value, array_merge(['safe' => 1, 'balanced' => 0], $options));
        }

        foreach ($value as $k => $v)
        {
            $value[$k] = static::xss_clean($v);
        }

        return $value;
    }

    /**
     * This method sanitize given value
     * @param mixed $value
     * @return mixed
     */
    public static function strip_tags($value)
    {
        if (!is_array($value))
        {
            $value = filter_var($value, FILTER_SANITIZE_STRING);
        }
        else
        {
            foreach ($value as $k => $v)
            {
                $value[$k] = static::strip_tags($v);
            }
        }

        return $value;
    }

    /**
     * This method perform htmlentities for given value
     * @param mixed $value
     * @param mixed $flags based on php::htmlentities
     * @param string $encoding based on php::htmlentities
     * @return string
     */
    public static function htmlentities($value, $flags = ENT_QUOTES, $encoding = 'UTF-8'): string
    {
        if (!is_array($value))
        {
            $value = \htmlentities($value, $flags, $encoding);
        }
        else
        {
            foreach ($value as $v)
            {
                $value = \htmlentities($v, $flags, $encoding);
            }
        }

        return $value;
    }
}
