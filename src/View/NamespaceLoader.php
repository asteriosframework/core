<?php declare(strict_types=1);

namespace Asterios\Core\View;

use Asterios\Core\Contracts\View\NamespaceLoaderInterface;
use Asterios\Core\Exception\ViewNamespaceLoaderException;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

class NamespaceLoader implements NamespaceLoaderInterface
{
    /**
     * @inheritDoc
     */
    public static function register(FilesystemLoader $loader, string $viewPath): void
    {
        $directories = scandir($viewPath);

        foreach ($directories as $dir)
        {
            if ($dir === '.' || $dir === '..')
            {
                continue;
            }

            $fullPath = $viewPath . '/' . $dir;

            if (is_dir($fullPath))
            {
                try
                {
                    $loader->addPath($fullPath, $dir);
                }
                catch (LoaderError $e)
                {
                    throw new ViewNamespaceLoaderException($e->getMessage());
                }
            }
        }
    }
}
