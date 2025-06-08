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

        public function normalizePath(string $path): string
        {
            return realpath(str_replace('/', DIRECTORY_SEPARATOR, $path));
        }

        public function config(string $key, mixed $default = null): mixed
        {
            /** @var \Asterios\Core\Config $config */
            $config = Bootstrap::$container->get(Config::class);

            // find configs
            $files = glob(normalize_path($config->get_config_path()) . DIRECTORY_SEPARATOR . '*.php');

            $item = $config->get_memory($key, $default);
            if ($item !== $default)
            {
                return $item;
            }

            foreach ($files as $file)
            {
                $item = $config->get(basename($file, '.php'), $key, $default);
                if ($item !== $default)
                {
                    return $item;
                }
            }

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
    use Asterios\Core\Helper\CoreHelper;

    function normalize_path(string $path): string
    {
        if (true === Bootstrap::$isInitialized)
        {
            try
            {
                /** @var CoreHelper $coreHelper */
                $coreHelper = Bootstrap::$container->get(CoreHelper::class);
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
        $coreHelper = Bootstrap::$container->get(CoreHelper::class);

        return $coreHelper->config($key, $default);
    }

    function app_path(string|null $extends = null): string
    {
        /** @var CoreHelper $coreHelper */
        $coreHelper = Bootstrap::$container->get(CoreHelper::class);
        return $coreHelper->appPath($extends);
    }

    function base_path(string|null $extends = null): string
    {
        /** @var CoreHelper $coreHelper */
        $coreHelper = Bootstrap::$container->get(CoreHelper::class);
        return $coreHelper->rootPath($extends);
    }
}
