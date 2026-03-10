<?php declare(strict_types=1);

namespace Asterios\Core\View\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigComponentExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [

            new TwigFunction('component', function (
                Environment $twig,
                string $component,
                array $data = []
            ) {
                return $twig->render("components/$component.twig", $data);

            }, [
                'needs_environment' => true,
                'is_safe' => ['html']
            ])

        ];
    }
}