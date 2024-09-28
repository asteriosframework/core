<?php declare(strict_types=1);

namespace Asterios\Core;

class Installer
{
    private string $installedFile = '.installed';
    protected string $envFile = '.env';

    /**
     * @var bool $runSeeder
     */
    protected bool $runSeeder = false;

    /**
     * @var string[] $errors
     */
    protected array $errors = [];

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

        if ($this->errors !== [])
        {
            Logger::forge()
                ->error("Install errors: " . implode(", ", $this->errors));
            Logger::forge()
                ->error('Installation aborted!');

            return false;
        }

        Logger::forge()
            ->info('Install application ...');

        if ($this->runSeeder)
        {
            Logger::forge()
                ->info('Initial application run with fresh migration and seeder.', ['timestamp' => $timestamp]);
        }

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
        $protectedDirectory = str_replace('/public', '', $this->getDocumentRoot());

        return $protectedDirectory . DIRECTORY_SEPARATOR . $this->installedFile;
    }

    public function createMediaFolders(): self
    {
        $env = (new Env($this->envFile));

        try
        {
            $mediaPaths = $env->getArrayPrefixed('MEDIA_');
        }
        catch (Exception\EnvException|Exception\EnvLoadException $e)
        {
            Logger::forge()
                ->error('Could not load MEDIA_ env variables!', ['exception' => $e->getTraceAsString()]);

            $this->errors[] = 'Could not load MEDIA_ variables from env file "' . $this->envFile . '"!';

            return $this;
        }

        $file = File::forge();

        $baseDirectory = $this->getDocumentRoot() . DIRECTORY_SEPARATOR;
        $mediaFolder = $baseDirectory . $mediaPaths['BASE_PATH'];
        $mediaImagesFolder = $baseDirectory . $mediaPaths['IMAGE_PATH'];
        $mediaGalleryFolder = $baseDirectory . $mediaPaths['GALLERY_PATH'];
        $mediaDocumentsFolder = $baseDirectory . $mediaPaths['FILES_PATH'];

        if (!$file->directory_exists($mediaFolder))
        {
            File::forge()
                ->create_directory($mediaFolder);

            Logger::forge()
                ->info('Created media directory "' . $mediaImagesFolder . '"');
        }

        if (!$file->directory_exists($mediaImagesFolder))
        {
            File::forge()
                ->create_directory($mediaImagesFolder);

            Logger::forge()
                ->info('Created media images directory "' . $mediaImagesFolder . '"');
        }

        if (!$file->directory_exists($mediaGalleryFolder))
        {
            File::forge()
                ->create_directory($mediaGalleryFolder);

            Logger::forge()
                ->info('Created media gallery directory "' . $mediaImagesFolder . '"');
        }

        if (!$file->directory_exists($mediaDocumentsFolder))
        {
            File::forge()
                ->create_directory($mediaDocumentsFolder);

            Logger::forge()
                ->info('Created media files directory ' . $mediaImagesFolder) . '"';
        }

        return $this;
    }

    /**
     * @return string
     */
    private function getDocumentRoot(): string
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }
}