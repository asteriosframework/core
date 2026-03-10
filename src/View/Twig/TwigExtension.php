<?php declare(strict_types=1);

namespace Asterios\Core\View\Twig;

use Asterios\Core\Asterios;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [

            new TwigFunction('base_path', fn() => Asterios::getBasePath()),

            new TwigFunction('asset', function(string $path)
            {
                return '/assets/' . ltrim($path, '/');
            }),

            new TwigFunction('route', function(string $path)
            {
                return '/' . ltrim($path, '/');
            }),

        ];
    }
}