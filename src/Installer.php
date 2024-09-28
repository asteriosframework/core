<?php declare(strict_types=1);

namespace Asterios\Core;

class Installer
{
    private string $installedFile = '.installed';
    protected string $envFile = '.env';

    public static function forge(string $env = '.env'): self
    {
        return new static($env);
    }

    final public function __construct(string $env = '.env')
    {
        $this->envFile = $env;
    }

    public function isInstalled(): bool
    {
        return File::forge()
            ->file_exists($this->getInstalledFile());
    }

    public function setIsInstalled(): bool
    {
        $timestamp = time();

        Logger::forge()
            ->info('Install application ...');
        Logger::forge()
            ->info('Initial application run with fresh migration and seeder.', ['timestamp' => $timestamp]);

        return File::forge()
            ->write($this->getInstalledFile(),
                Cast::forge()
                    ->string($timestamp));
    }

    /**
     * @return string
     */
    public function getInstalledFile(): string
    {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];

        $protectedDirectory = str_replace('/public', '', $documentRoot);

        return $protectedDirectory . DIRECTORY_SEPARATOR . $this->installedFile;
    }

    public function createMediaFolders(): self
    {
        $env = (new Env($this->envFile));

        $mediaPaths = [];

        try
        {
            $mediaPaths = $env->getArrayPrefixed('MEDIA_');
        }
        catch (Exception\EnvException|Exception\EnvLoadException $e)
        {
            Logger::forge()
                ->fatal('Could not load MEDIA_ env variables!', ['exception' => $e->getTraceAsString()]);
        }

        $file = File::forge();

        $mediaFolder = $mediaPaths['BASE_PATH'];
        $mediaImagesFolder = $mediaPaths['IMAGE_PATH'];
        $mediaGalleryFolder = $mediaPaths['GALLERY_PATH'];
        $mediaDocumentsFolder = $mediaPaths['FILES_PATH'];

        if (!$file->directory_exists($mediaFolder))
        {
            File::forge()
                ->create_directory($mediaFolder);
        }

        if (!$file->directory_exists($mediaImagesFolder))
        {
            File::forge()
                ->create_directory($mediaImagesFolder);
        }

        if (!$file->directory_exists($mediaGalleryFolder))
        {
            File::forge()
                ->create_directory($mediaGalleryFolder);
        }

        if (!$file->directory_exists($mediaDocumentsFolder))
        {
            File::forge()
                ->create_directory($mediaDocumentsFolder);
        }

        return $this;
    }
}