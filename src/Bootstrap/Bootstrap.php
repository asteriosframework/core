<?php

declare(strict_types=1);

namespace Asterios\Core\Bootstrap;

use Throwable;

error_reporting(-1);
ini_set('display_errors', 1);

use Asterios\Core\Asterios;
use Asterios\Core\Config;
use Asterios\Core\DI\Container;
use Asterios\Core\Env;
use Asterios\Core\Helper\CoreHelper;

final class Bootstrap
{
    public static bool $isInitialized = false;
    public static Container $container;
    public static Asterios $asterios;
    public static string $basePath;

    public static function isInitialized(): bool
    {
        return self::$isInitialized;
    }

    public static function getAsterios(): Asterios
    {
        return self::$asterios;
    }

    public static function getContainer(): Container
    {
        return self::$container ?? self::$container = new Container();
    }

    public static function getBasePath(): string
    {
        return self::$basePath;
    }

    public static function init(string $basePath): void
    {
        self::$basePath = $basePath;

        try
        {
            self::getContainer()
                ->set(CoreHelper::class, CoreHelper::class, ['basePath' => self::getBasePath()])
                ->set(Config::class, Config::class)
                ->set(Env::class, Env::class, ['envFile' => self::getBasePath() . '/.env']);

            /** @var \Asterios\Core\Config $configObj */
            $configObj = self::$container->get(Config::class);
            $configObj->set_config_path(normalize_path(self::$basePath . '/config'));

            self::$asterios = new Asterios();
            self::$asterios->setEnvironment(env('ENVIRONMENT', Asterios::DEVELOPMENT));
            self::$asterios->setTimezone(env('TIMEZONE', 'UTC'));

            /**
             * @var  CoreHelper $configHelper
             */
            $configHelper = self::$container->get(CoreHelper::class);
            $configHelper->loadConfig();

            self::$asterios->init();

            self::$container->set(Asterios::class, self::$asterios);

            self::$isInitialized = true;
        }
        catch (Throwable $e)
        {
            echo $e->getMessage() . PHP_EOL;
            echo 'Execution abort' . PHP_EOL;
        }
    }
}
