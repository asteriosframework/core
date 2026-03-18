<?php declare(strict_types=1);

namespace Asterios\Core\View\Twig;

use Asterios\Core\Asterios;
use Asterios\Core\Contracts\View\Twig\TwigManagerInterface;
use Asterios\Core\Env;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Exception\TwigTemplateManagerException;
use Asterios\Core\View\NamespaceLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class TwigManager implements TwigManagerInterface
{
    private static ?Environment $twig = null;
    private static ?string $cachePath = null;

    /**
     * @inheritDoc
     */
    public static function getTwig(Env $env): Environment
    {
        if (self::$twig !== null)
        {
            return self::$twig;
        }

        $base = Asterios::getBasePath();

        try
        {
            $templatePath = $base . $env->get('TEMPLATE_PATH');
            $twigDebug    = filter_var($env->get('TWIG_DEBUG'), FILTER_VALIDATE_BOOLEAN);
            $twigAutoReload = filter_var($env->get('TWIG_AUTO_RELOAD'), FILTER_VALIDATE_BOOLEAN);
            $twigCacheEnabled =  filter_var($env->get('TWIG_CACHE_ENABLED'), FILTER_VALIDATE_BOOLEAN);
        }
        catch (EnvException|EnvLoadException $e)
        {
            throw new TwigTemplateManagerException($e->getMessage());
        }

        self::$cachePath = self::resolveCachePath($env);

        if (
            $twigCacheEnabled &&
            !is_dir(self::getCachePath()) &&
            !mkdir(self::getCachePath(), 0777, true)
            && !is_dir(self::getCachePath())
        )
        {
            throw new TwigTemplateManagerException(sprintf('Directory "%s" was not created', self::getCachePath()));
        }

        $loader = new FilesystemLoader($templatePath);
        NamespaceLoader::register($loader, $templatePath);

        self::$twig = new Environment($loader, [
            'cache'       => $twigCacheEnabled ? self::getCachePath() : false,
            'debug'       => $twigDebug,
            'auto_reload' => $twigAutoReload,
            'autoescape'  => 'html'
        ]);

        if ($twigDebug)
        {
            self::$twig->addExtension(new DebugExtension());
        }

        self::$twig->addExtension(new TwigExtension());
        self::$twig->addExtension(new TwigDirectiveExtension());
        self::$twig->addExtension(new TwigComponentExtension());

        return self::$twig;
    }

    /**
     * @inheritDoc
     */
    public static function getCachePath(): ?string
    {
        return self::$cachePath;
    }

    /**
     * @inheritDoc
     */
    public static function resolveCachePath(Env $env): string
    {
        try {
            return Asterios::getBasePath() . $env->get('TWIG_CACHE');
        } catch (EnvException|EnvLoadException $e) {
            throw new TwigTemplateManagerException($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public static function clearCache(Env $env): void
    {
        self::$cachePath = self::resolveCachePath($env);

        if (!self::getCachePath() || !is_dir(self::getCachePath()))
        {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(self::getCachePath(), \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file)
        {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
    }

    /**
     * @inheritDoc
     */
    public static function warmupCache(Environment $twig): void
    {
        $loader = $twig->getLoader();

        if (!$loader instanceof FilesystemLoader)
        {
            return;
        }

        foreach ($loader->getPaths() as $path)
        {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path)
            );

            foreach ($iterator as $file)
            {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.twig'))
                {
                    $template = str_replace($path . DIRECTORY_SEPARATOR, '', $file->getPathname());

                    try
                    {
                        $twig->load($template);
                    }
                    catch (\Throwable $e)
                    {
                        throw new TwigTemplateManagerException($e->getMessage());
                    }
                }
            }
        }
    }
}
