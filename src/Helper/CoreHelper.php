<?php

declare(strict_types=1);

namespace Asterios\Core\Helper {
    use Asterios\Core\Config;
    use Asterios\Core\Bootstrap\Bootstrap;

    final class CoreHelper
    {
        public function __construct(protected string $basePath)
        {
            //
        }

        public function loadConfig(): void
        {
            /** @var \Asterios\Core\Config $config */
            $config = Bootstrap::getContainer()->get(Config::class);

            // load default config
            $defaultConfig = $config::load('default');
            foreach ($defaultConfig as $key => $value)
            {
                // set the config in memory
                $config::set_memory($key, $value);
            }

            // find configs
            $files = glob(normalize_path($config->get_config_path()) . DIRECTORY_SEPARATOR . '*.php');
            foreach ($files as $file)
            {
                $filename = basename($file, '.php');
                if ($filename === 'default')
                {
                    // skip default config, already loaded
                    continue;
                }

                $prefix = $filename . '.';
                $loadedConfig = $config::load($filename);

                foreach ($loadedConfig as $key => $value)
                {
                    // set the config in memory
                    $config::set_memory($prefix . $key, $value);
                }
            }

        }

        public function normalizePath(string $path): string
        {
            return realpath(str_replace('/', DIRECTORY_SEPARATOR, $path));
        }

        public function config(string $key, mixed $default = null): mixed
        {
            /** @var \Asterios\Core\Config $config */
            $config = Bootstrap::getContainer()->get(Config::class);

            $item = $config->get_memory($key, $default);
            if ($item !== $default)
            {
                return $item;
            }

            // If the key is not found, return the default value
            return $default;
        }

        public function appPath(string|null $extends = null): string
        {
            $path = "{$this->basePath}/app";

            if (null !== $extends)
            {
                $path .= "/$extends";
            }

            return realpath(normalize_path($path));
        }

        public function rootPath(string|null $extends = null): string
        {
            $path = $this->basePath;

            if (null !== $extends)
            {
                $path .= '/' . $extends;
            }

            return realpath(normalize_path($path));
        }
    }
}

namespace {
    use Asterios\Core\Config;
    use Asterios\Core\Bootstrap\Bootstrap;
    use Asterios\Core\DI\Exceptions\NotFoundException;
    use Asterios\Core\Env;
    use Asterios\Core\Helper\CoreHelper;

    function env(string $key, mixed $default = null): array|bool|int|string|null
    {
        /** @var Env $env */
        $env = Bootstrap::getContainer()->get(Env::class);

        return $env->get($key, $default);
    }

    function normalize_path(string $path): string
    {
        if (true === Bootstrap::isInitialized())
        {
            try
            {
                /** @var CoreHelper $coreHelper */
                $coreHelper = Bootstrap::getContainer()->get(CoreHelper::class);
                return $coreHelper->normalizePath($path);
            }
            catch (NotFoundException)
            {
                //
            }
        }

        return (new CoreHelper(''))->normalizePath($path);
    }

    function config(string $key, mixed $default = null): mixed
    {
        /** @var CoreHelper $coreHelper */
        $coreHelper = Bootstrap::getContainer()->get(CoreHelper::class);

        return $coreHelper->config($key, $default);
    }

    function app_path(string|null $extends = null): string
    {
        /** @var CoreHelper $coreHelper */
        $coreHelper = Bootstrap::getContainer()->get(CoreHelper::class);
        return $coreHelper->appPath($extends);
    }

    function base_path(string|null $extends = null): string
    {
        /** @var CoreHelper $coreHelper */
        $coreHelper = Bootstrap::getContainer()->get(CoreHelper::class);
        return $coreHelper->rootPath($extends);
    }

    /**
     * Method autoloader
     *
     * @param array<int,class-string> $classes
     * @param string $prefix
     * @param string $baseDir
     *
     * @return void
     */
    function autoloader(array $classes, string $prefix = 'Asterios\\Core\\', string $baseDir = __DIR__ . '/../'): void
    {
        foreach ($classes as $class)
        {
            // does the class use the namespace prefix?
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0)
            {
                return;
            }

            // get the relative class name
            $relativeClass = substr($class, $len);

            // replace namespace prefix with base directory, replace namespace
            // separators with directory separators in the relative class name,
            // append with .php
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            // if the file exists, require it
            if (file_exists($file))
            {
                require_once $file;
            }
        }
    }
}
