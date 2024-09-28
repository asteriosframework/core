<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Dto\DbMigrationDto;
use Asterios\Core\Interfaces\InstallerInterface;

class Installer implements InstallerInterface
{
    private string $installedFile = '.installed';
    protected string $envFile = '.env';

    protected bool $runDatabaseSeeder = false;

    protected bool $runDatabaseMigrations = false;

    protected DbMigrationDto|null $dto;

    /**
     * @var string[] $errors
     */
    protected array $errors = [];

    public static function forge(string $env = '.env', DbMigrationDto $dto = null): self
    {
        return new static($env);
    }

    final public function __construct(string $env = '.env', DbMigrationDto $dto = null)
    {
        $this->envFile = $env;
        $this->dto = $dto;
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
                ->error("Install errors: " . implode(', ', $this->errors));
            Logger::forge()
                ->error('Installation aborted!');

            return false;
        }

        Logger::forge()
            ->info('Install application ...');

        if ($this->runDatabaseSeeder)
        {
            Logger::forge()
                ->info('Initial application run with fresh database seeder.', ['timestamp' => $timestamp]);
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
                ->info('Created media files directory ' . $mediaImagesFolder . '"');
        }

        return $this;
    }

    public function setRunDatabaseSeeder(bool $value): self
    {
        $this->runDatabaseSeeder = $value;

        return $this;
    }

    public function setRunDatabaseMigrations(bool $value): self
    {
        $this->runDatabaseMigrations = $value;

        return $this;
    }

    public function runDbMigrations(): self
    {
        return $this;
    }

    public function runDbSeeders(): self
    {

        return $this;
    }

    public function run(bool $createMediaFolders = false, bool $runDbMigration = false, bool $runDbSeeder = false): bool
    {
        if ($createMediaFolders)
        {
            $this->createMediaFolders();
        }

        if ($runDbMigration)
        {
            $this->runDbMigrations();
        }

        if ($runDbSeeder)
        {
            $this->runDbSeeders();
        }

        if ($this->errors !== [])
        {
            return false;
        }

        $this->setIsInstalled();

        if (!$this->isInstalled())
        {
            Logger::forge()
                ->error("Install errors: " . implode(', ', $this->errors));

            return false;
        }

        return true;
    }

    private function getDocumentRoot(): string
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }
}