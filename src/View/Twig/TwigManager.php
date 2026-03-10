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
        }
        catch (EnvException|EnvLoadException $e)
        {
            throw new TwigTemplateManagerException($e->getMessage());
        }

        $cachePath = $base . $env->get('TWIG_CACHE');

        $loader = new FilesystemLoader($templatePath);

        NamespaceLoader::register($loader, $templatePath);

        self::$twig = new Environment($loader, [
            'cache' => is_dir($cachePath) ? $cachePath : false,
            'debug' => filter_var($env->get('TWIG_DEBUG'), FILTER_VALIDATE_BOOLEAN),
            'autoescape' => 'html'
        ]);

        try
        {
            if ($env->get('TWIG_DEBUG'))
            {
                self::$twig->addExtension(new DebugExtension());
            }
        }
        catch (EnvException|EnvLoadException $e)
        {
            throw new TwigTemplateManagerException($e->getMessage());
        }

        self::$twig->addExtension(new TwigExtension());
        self::$twig->addExtension(new TwigDirectiveExtension());
        self::$twig->addExtension(new TwigComponentExtension());

        return self::$twig;
    }
}
