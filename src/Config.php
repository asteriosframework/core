<?php
declare(strict_types=1);

namespace Asterios\Core;

use ArrayAccess;
use Asterios\Core\Exception\ConfigLoadException;

class Config
{
    /**
     * @var string
     */
    static protected $config_path;

    /**
     * @var array
     */
    static protected $memory_items = [];

    /**
     * @param string $config_path
     */
    public static function set_config_path(string $config_path): void
    {
        self::$config_path = $config_path;
    }

    /**
     * @return string
     */
    public static function get_config_path(): string
    {
        return self::$config_path;
    }

    /**
     * Loads a config file.
     *
     * @param string $file
     * @return array
     * @throws ConfigLoadException
     */
    public static function load(string $file): array
    {
        $config_path = self::get_config_path();
        $environment = Asterios::get_environment();
        $environment_config_file = [];

        $default_config_file_path = $config_path . DIRECTORY_SEPARATOR . $file . '.php';
        $environment_config_file_path = $config_path . DIRECTORY_SEPARATOR . $environment . DIRECTORY_SEPARATOR . $file . '.php';

        if (!File::forge()
            ->file_exists($default_config_file_path))
        {
            throw new ConfigLoadException('Could not load default config file ' . $default_config_file_path);
        }

        $default_config_file = File::forge()
            ->load_file($default_config_file_path);

        if (File::forge()
            ->file_exists($environment_config_file_path))
        {
            $environment_config_file = File::forge()
                ->load_file($environment_config_file_path);
        }

        return array_merge($default_config_file, $environment_config_file);
    }

    /**
     * Get config value from given config file.
     *
     * @param string $file required
     * @param string|null $item The name of the item to retrieve. Groups and multi-dimensional arrays can be accessed by separating the levels by a dot
     * @param mixed $default If given item is not found, the default value will be returned
     * @return mixed|object
     * @throws ConfigLoadException
     */
    public static function get(string $file, ?string $item = null, $default = null)
    {
        $config_object = self::load($file);

        if (null === $item)
        {
            return (object)$config_object;
        }

        $prepare_config = explode('.', $item);

        foreach ($prepare_config as $segment)
        {
            if (static::accessible($config_object) && static::exists($config_object, $segment))
            {
                $config_object = $config_object[$segment];
            }
            else
            {
                return $default;
            }
        }

        return (is_array($config_object)) ? (object)$config_object : $config_object;
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     * @return bool
     */
    protected static function accessible($value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array $array
     * @param string|int $key
     * @return bool
     */
    public static function exists(array $array, $key): bool
    {
        return array_key_exists($key, $array);
    }

    /**
     * @param string $item
     * @param mixed $value
     */
    public static function set_memory(string $item, $value): void
    {
        self::$memory_items[$item] = $value;
    }

    /**
     * @param string $item
     * @param mixed $default
     * @return mixed
     */
    public static function get_memory(string $item, $default = null)
    {
        return self::$memory_items[$item] ?? $default;
    }
}
