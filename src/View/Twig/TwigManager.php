<?php declare(strict_types=1);

namespace Asterios\Core\View\Twig;

use Asterios\Core\Asterios;
use Asterios\Core\Env;
use Asterios\Core\View\NamespaceLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class TwigManager
{
    private static ?Environment $twig = null;

    public static function getTwig(Env $env): Environment
    {
        if (self::$twig !== null)
        {
            return self::$twig;
        }

        $base = Asterios::getBasePath();

        $templatePath = $base . $env->get('TEMPLATE_PATH');
        $cachePath = $base . $env->get('TWIG_CACHE');

        $loader = new FilesystemLoader($templatePath);

        NamespaceLoader::register($loader, $templatePath);

        self::$twig = new Environment($loader, [
            'cache' => is_dir($cachePath) ? $cachePath : false,
            'debug' => filter_var($env->get('TWIG_DEBUG'), FILTER_VALIDATE_BOOLEAN),
            'autoescape' => 'html'
        ]);

        if ($env->get('TWIG_DEBUG'))
        {
            self::$twig->addExtension(new DebugExtension());
        }

        self::$twig->addExtension(new TwigExtension());
        self::$twig->addExtension(new TwigDirectiveExtension());
        self::$twig->addExtension(new TwigComponentExtension());

        return self::$twig;
    }
}