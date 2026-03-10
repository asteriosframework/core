<?php declare(strict_types=1);

namespace Asterios\Core\View;

use Twig\Loader\FilesystemLoader;

class NamespaceLoader
{
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
                $loader->addPath($fullPath, $dir);
            }
        }
    }
}