<?php declare(strict_types=1);

namespace Asterios\Core\View\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigDirectiveExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [

            new TwigFunction('auth', function () {
                return isset($_SESSION['user']);
            }),

            new TwigFunction('guest', function () {
                return !isset($_SESSION['user']);
            }),

            new TwigFunction('csrf', function () {
                if (!isset($_SESSION['_csrf']))
                {
                    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
                }

                return '<input type="hidden" name="_csrf" value="' . $_SESSION['_csrf'] . '">';
            }, ['is_safe' => ['html']]),

        ];
    }
}