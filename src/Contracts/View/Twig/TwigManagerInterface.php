<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\View\Twig;

use Asterios\Core\Env;
use Asterios\Core\Exception\TwigTemplateManagerException;
use Asterios\Core\Exception\ViewNamespaceLoaderException;
use Twig\Environment;

interface TwigManagerInterface
{
    /**
     * @param Env $env
     * @return Environment
     * @throws ViewNamespaceLoaderException
     * @throws TwigTemplateManagerException
     */
    public static function getTwig(Env $env): Environment;

    /**
     * @return string|null
     */
    public static function getCachePath(): ?string;

    /**
     * @return void
     */
    public static function clearCache(): void;

    /**
     * @param Environment $twig
     * @return void
     * @throws TwigTemplateManagerException
     */
    public static function warmupCache(Environment $twig): void;
}
