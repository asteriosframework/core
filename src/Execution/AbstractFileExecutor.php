<?php declare(strict_types=1);

namespace Asterios\Core\Execution;

use Asterios\Core\Asterios;
use Asterios\Core\Env;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;
use Asterios\Core\Exception\LoggerException;
use Asterios\Core\Logger;

abstract class AbstractFileExecutor
{
    protected array $errors = [];

    protected array $messages = [];

    protected string $envFile = '.env';

    protected ?Env $env = null;

    protected PathResolver $pathResolver;

    /**
     * @param string $msg
     * @return void
     * @throws LoggerException
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
     * @throws LoggerException
     */
    protected function getPathsFromEnv(string $key): ?string
    {
        try
        {
            return $this->env?->get($key);
        }
        catch (EnvException|EnvLoadException $e)
        {
            $this->logError(
                sprintf(
                    'Env error [%s]: %s',
                    $key,
                    $e->getMessage()
                )
            );

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
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return string[]
     */
    protected function getPhpFiles(string $path): array
    {
        if (!is_dir($path))
        {
            return [];
        }

        $files = glob($path . '/*.php');

        if (!is_array($files))
        {
            return [];
        }

        sort($files);

        return $files;
    }

    /**
     * @return string[]
     */
    protected function getPhpFilesReverse(string $path): array
    {
        $files = $this->getPhpFiles($path);

        rsort($files);

        return $files;
    }

    /**
     * @param string $file
     * @return mixed
     */
    protected function loadPhpFile(string $file): mixed
    {
        return require $file;
    }
}