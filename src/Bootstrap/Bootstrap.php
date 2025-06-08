<?php declare(strict_types=1);

namespace Asterios\Core\Bootstrap;

use Throwable;

require __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, '../../vendor/autoload.php');
require __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, '../Helper/CoreHelper.php');

error_reporting(-1);
ini_set('display_errors', 1);

use Asterios\Core\Asterios;
use Asterios\Core\Config;
use Asterios\Core\DI\Container;
use Asterios\Core\Helper\CoreHelper;

final class Bootstrap
{
    public static bool $isInitialized = false;
    public static Container $container;
    public static Asterios $asterios;
    public static string $basePath;

    public static function init(string $basePath): void
    {
        self::$basePath = $basePath;

        try
        {
            self::$container = new Container();

            self::$container->set(CoreHelper::class, CoreHelper::class, ['basePath' => $basePath]);

            self::$container->set(Config::class, Config::class);

            /** @var \Asterios\Core\Config $configObj */
            $configObj = self::$container->get(Config::class);
            $configObj->set_config_path(normalize_path(self::$basePath . '/config'));

            self::$container->set(Asterios::class, Asterios::class);
            self::$asterios = self::$container->get(Asterios::class);

            /**
             * @var object{environment:string, timezone: string}
             */
            $config = self::$asterios->config();

            self::$asterios->setEnvironment($config->environment);
            self::$asterios->setTimezone($config->timezone);

            self::$asterios->init();

            self::$isInitialized = true;
        }
        catch (Throwable $e)
        {
            echo $e->getMessage() . PHP_EOL;
            echo 'Execution abort' . PHP_EOL;
        }
    }
}

