<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\View;

use Asterios\Core\Exception\ViewNamespaceLoaderException;
use Twig\Loader\FilesystemLoader;

interface NamespaceLoaderInterface
{
    /**
     * @param FilesystemLoader $loader
     * @param string $viewPath
     * @return void
     * @throws ViewNamespaceLoaderException
     */
    public static function register(FilesystemLoader $loader, string $viewPath): void;
}