<?php declare(strict_types=1);

namespace Asterios\Core\Db;

use Asterios\Core\Asterios;
use Asterios\Core\Config;
use Asterios\Core\Contracts\SeederInterface;
use Asterios\Core\Db;
use Asterios\Core\Env;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Exception\MigrationException;
use Asterios\Core\Logger;

class Seeder implements SeederInterface
{
    protected array $errors = [];
    protected array $messages = [];
    protected string $envFile = '.env';
    /**
     * @var array<string, array<int, string>>
     */
    protected array $seeder = [];

    protected ?Env $env = null;

    public function __construct(string $envFile = '.env')
    {
        $this->envFile = Asterios::getBasePath() . DIRECTORY_SEPARATOR . $envFile;
        Config::set_config_path(Asterios::getBasePath() . DIRECTORY_SEPARATOR . 'config');

        if (null === $this->env)
        {
            $this->env = new Env($this->envFile);
        }

    }

    /**
     * @inheritDoc
     */
    public function seed(bool $truncateTables = true): bool
    {
        $seederPath = $this->getSeederPath();

        if (null === $seederPath)
        {
            $this->logError('Could not load seeder path.');

            return false;
        }

        if (empty($this->getSeeder()))
        {
            try
            {
                $this->getSeederFromConfig();
            } catch (ConfigLoadException|MigrationException $e)
            {
                $this->logError('Error loading seeder config file: ' . $e->getMessage());

                return false;
            }
        }

        $files = $this->getSeederFilesInOrder($this->getSeeder(), $seederPath);

        foreach ($files as $file)
        {
            try
            {
                $table = pathinfo($file, PATHINFO_FILENAME);
                $fileNameForLogger = pathinfo($file, PATHINFO_BASENAME);

                Db::write('SET FOREIGN_KEY_CHECKS = 0;');

                if ($truncateTables)
                {
                    Db::write("TRUNCATE `$table`;");
                }

                Db::forge()
                    ->seedFromFile($file);

                $this->messages[][$fileNameForLogger] = 'done';

                Db::write('SET FOREIGN_KEY_CHECKS = 1;');

                Logger::forge()
                    ->info('Seeded: ' . $fileNameForLogger);
                usleep(1000);
            } catch (\JsonException|ConfigLoadException $e)
            {
                $this->logError('Error Seeder for ' . $fileNameForLogger . ':' . $e->getMessage());
                $this->messages[][$fileNameForLogger] = 'failed';

                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSeederPath(): ?string
    {
        $seederPath = $this->getPathsFromEnv('DATABASE_SEEDER_PATH');

        return $seederPath ? $this->getProtectedPath() . $seederPath : null;
    }

    /**
     * @inheritDoc
     */
    public function setSeeder(array $seeder): void
    {
        $this->seeder = $seeder;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritDoc
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param string $msg
     * @return void
     */
    protected function logError(string $msg): void
    {
        Logger::forge()
            ->error($msg);
        $this->errors[] = $msg;
    }

    /**
     * @param string $key
     * @return string|null
     */
    protected function getPathsFromEnv(string $key): ?string
    {
        try
        {
            return $this->env->get($key);
        } catch (EnvException|EnvLoadException $e)
        {
            $this->logError("Env-Fehler [$key]: " . $e->getMessage());

            return null;
        }
    }

    /**
     * @return string
     */
    protected function getProtectedPath(): string
    {
        return Asterios::getBasePath();
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function getSeeder(): array
    {
        return $this->seeder;
    }

    /**
     * @param array $seederFiles
     * @param string $seederPath
     * @return array
     */
    private function getSeederFilesInOrder(array $seederFiles, string $seederPath): array
    {
        $files = scandir($seederPath);

        $validFiles = array_filter($files, static function ($file) use ($seederPath) {
            return $file !== '.' && $file !== '..' && is_file($seederPath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'json';
        });

        $orderedFiles = $this->sortFilesByDependencies($validFiles, $seederFiles);

        return array_map(static fn($file) => $seederPath . '/' . $file, $orderedFiles);
    }

    /**
     * @param array $files
     * @param array $dependencies
     * @return array
     */
    private function sortFilesByDependencies(array $files, array $dependencies): array
    {
        $sorted = [];
        $visited = [];

        $visit = static function ($file) use ($dependencies, &$visited, &$sorted, &$visit) {
            if (isset($visited[$file]))
                return;

            $visited[$file] = true;

            if (isset($dependencies[$file]))
            {
                foreach ($dependencies[$file] as $dependency)
                {
                    if (!isset($visited[$dependency]))
                    {
                        $visit($dependency);
                    }
                }
            }

            $sorted[] = $file;
        };

        foreach ($files as $file)
        {
            $visit($file);
        }

        return $sorted;
    }

    /**
     * @return void
     * @throws MigrationException|ConfigLoadException
     */
    private function getSeederFromConfig(): void
    {
        $seeder = (array)Config::get('seeder');

        if (empty($seeder))
        {
            throw new MigrationException('Seeder config file is empty!');
        }

        $this->setSeeder($seeder);
    }
}
