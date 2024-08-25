<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Exception\AsteriosException;

class Asterios
{
    /**
     * @var  string  The version of Asterios PHP Framework
     */
    public const VERSION = '1.1.0';

    /**
     * @var  string  The version of Asterios PHP Framework
     */
    public const NAME = 'Asterios PHP Framework';

    /**
     * @var  string  constant used for when in development
     */
    public const DEVELOPMENT = 'development';

    /**
     * @var  string  constant used for when in testing mode
     */
    public const TEST = 'test';

    /**
     * @var  string  constant used for when testing the app on a staging system.
     */
    public const STAGING = 'staging';

    /** @var string constant used for feature environment */
    public const FEATURE = 'feature';

    /**
     * @var  string  constant used for when in production
     */
    public const PRODUCTION = 'production';

    /**
     * @var  string  The Asterios environment
     */
    private static string $environment = Asterios::PRODUCTION;

    /**
     * @var string Encoding
     * */
    private static string $encoding = 'UTF-8';

    /**
     * @var bool It will be true if Asterios has been initialized
     */
    private static bool $initialized = false;

    /**
     * @param string $environment
     */
    public static function setEnvironment(string $environment): void
    {
        self::$environment = $environment;
    }

    /**
     * @return string
     */
    public static function getEnvironment(): string
    {
        return self::$environment;
    }

    /**
     * Initializes the framework. This can only be called once.
     * @return    void
     * @throws AsteriosException
     * @throws Exception\ConfigLoadException
     */
    public static function init(): void
    {
        if (self::$initialized)
        {
            throw new AsteriosException('You can\'t initialize Asterios more than once.');
        }

        $security_check = self::config('security.input_filter');

        if (empty($security_check) && self::getEnvironment() === self::DEVELOPMENT)
        {
            // @codeCoverageIgnoreStart
            Logger::forge()
                ->info('Warning: Using your application without input filtering is a security risk!');
            // @codeCoverageIgnoreEnd
        }
        else
        {
            // Run Input Filtering
            Security::clean_input();
        }

        self::$initialized = true;
    }

    /**
     * This method set the timezone. If timezone is null, the timezone from the configuration file will be used.
     * @param string $timezone
     */
    public static function setTimezone(string $timezone): void
    {
        (new Date())->setTimezone($timezone);
    }

    /**
     * This method will return the defined timezone.
     * @return string
     */
    public static function getTimezone(): string
    {
        return (new Date())->getTimezone();
    }

    /**
     * This method return given config value that is stored in the default.json config file.
     * If no value is given, the whole config data will be returned as object
     * @param string|null $item
     * @param string $config_file
     * @return mixed
     * @throws Exception\ConfigLoadException
     */
    public static function config(?string $item = null, string $config_file = 'default'): mixed
    {
        return Config::get($config_file, $item);
    }

    /**
     * @return string
     */
    public static function getEncoding(): string
    {
        return self::$encoding;
    }

    /**
     * @param string $encoding
     */
    public static function setEncoding(string $encoding): void
    {
        self::$encoding = $encoding;
    }

    /**
     * @return bool
     */
    public static function isInitialized(): bool
    {
        return self::$initialized;
    }

    public static function isProduction(): bool
    {
        return self::$environment === self::PRODUCTION;
    }

    public static function isStaging(): bool
    {
        return self::$environment === self::STAGING;
    }

    public static function isFeature(): bool
    {
        return self::$environment === self::FEATURE;
    }

    public static function isDevelopment(): bool
    {
        return self::$environment === self::DEVELOPMENT;
    }
}