<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Contracts\InstallerInterface;
use Asterios\Core\Db\Migration;
use Asterios\Core\Db\Seeder;
use Asterios\Core\Enum\MediaModeEnum;

class Installer implements InstallerInterface
{
    private string $installedFile = '.installed';
    protected string $envFile = '.env';
    protected bool $runDatabaseSeeder = false;
    protected bool $runDatabaseMigrations = false;

    /**
     * @var string[] $errors
     */
    protected array $errors = [];

    public static function forge(string $envFile = '.env'): self
    {
        return new static($envFile);
    }

    final public function __construct(string $envFile = '.env')
    {
        $this->envFile = $envFile;
    }

    /**
     * @inheritDoc
     */
    public function isInstalled(): bool
    {
        return File::forge()
            ->file_exists($this->getInstalledFile());
    }

    /**
     * @inheritDoc
     */
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

        return File::forge()
            ->write($this->getInstalledFile(),
                Cast::forge()
                    ->string($timestamp));
    }

    /**
     * @inheritDoc
     */
    public function getInstalledFile(): string
    {
        $protectedDirectory = str_replace('/public', '', Asterios::getDocumentRoot());

        return $protectedDirectory . DIRECTORY_SEPARATOR . $this->installedFile;
    }

    /**
     * @inheritDoc
     */
    public function createMediaFolders(): self
    {
        $env = (new Env($this->envFile));

        try
        {
            $mediaPaths = $env->getArrayPrefixed('MEDIA_');
        } catch (Exception\EnvException|Exception\EnvLoadException $e)
        {
            Logger::forge()
                ->error('Could not load MEDIA_ env variables!', ['exception' => $e->getTraceAsString()]);

            $this->errors[] = 'Could not load MEDIA_ variables from env file "' . $this->envFile . '"!';

            return $this;
        }

        $baseDirectory = Asterios::getDocumentRoot() . DIRECTORY_SEPARATOR;
        $mediaFolder = $baseDirectory . $mediaPaths['BASE_PATH'];
        $mediaImagesFolder = $baseDirectory . $mediaPaths['IMAGE_PATH'];
        $mediaGalleryFolder = $baseDirectory . $mediaPaths['GALLERY_PATH'];
        $mediaDocumentsFolder = $baseDirectory . $mediaPaths['FILES_PATH'];

        $this->createMediaFolder($mediaFolder, MediaModeEnum::BASE);
        $this->createMediaFolder($mediaImagesFolder, MediaModeEnum::IMAGE);
        $this->createMediaFolder($mediaGalleryFolder, MediaModeEnum::GALLERY);
        $this->createMediaFolder($mediaDocumentsFolder, MediaModeEnum::DOCUMENT);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRunDatabaseSeeder(bool $value): self
    {
        $this->runDatabaseSeeder = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRunDatabaseMigrations(bool $value): self
    {
        $this->runDatabaseMigrations = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function runDbMigrations(): self
    {
        Logger::forge()
            ->info('Starting database migration ...');

        usleep(1000);

        $migration = (new Migration($this->envFile));

        $result = $migration->migrate();

        if (!$result)
        {
            Logger::forge()
                ->error('Database migration failed.', ['error' => $migration->getErrors()]);

            return $this;
        }

        usleep(1000);

        Logger::forge()
            ->info('Database migration complete!');

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function runDbSeeders(bool $truncateTables = true): self
    {

        Logger::forge()
            ->info('Starting database seeding ...');

        usleep(1000);

        $seeder = (new Seeder($this->envFile));

        $result = $seeder->seed($truncateTables);

        if (!$result)
        {
            Logger::forge()
                ->error('Database seeding failed.', ['error' => $seeder->getErrors()]);

            return $this;
        }

        usleep(1000);

        Logger::forge()
            ->info('Database seeding complete!');

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function run(bool $createMediaFolders = false, bool $runDbMigration = false, bool $runDbSeeder = false, bool $truncateTables = true): bool
    {
        Logger::forge()
            ->info('Install application ...');

        usleep(1000);

        if ($createMediaFolders)
        {
            $this->createMediaFolders();

            usleep(1000);
        }

        $this->setRunDatabaseMigrations($runDbMigration);

        if ($this->runDatabaseMigrations)
        {
            $this->runDbMigrations();

            usleep(1000);
        }

        $this->setRunDatabaseSeeder($runDbSeeder);

        if ($this->runDatabaseSeeder)
        {
            $this->runDbSeeders();

            usleep(1000);
        }

        if ($this->errors !== [])
        {
            return false;
        }

        $this->setIsInstalled();

        usleep(1000);

        if (!$this->isInstalled())
        {
            Logger::forge()
                ->error("Install errors: " . implode(', ', $this->errors));

            return false;
        }

        Logger::forge()
            ->info('Installation complete!');

        return true;
    }

    /**
     * @inheritDoc
     */
    public function createMediaFolder(string $mediaFolder, MediaModeEnum $type): bool
    {
        $file = File::forge();

        if (!$file->directory_exists($mediaFolder))
        {
            $success = $file->create_directory($mediaFolder);

            if (!$success)
            {
                Logger::forge()
                    ->error('Could not create  media ' . $type->mode() . ' directory "' . $mediaFolder . '"!');

                $this->errors[] = 'Could not create  media ' . $type->mode() . ' directory "' . $mediaFolder . '"!';

                return false;
            }

            Logger::forge()
                ->info('Created media ' . $type->mode() . ' directory "' . $mediaFolder . '".');
        }

        return true;
    }
}