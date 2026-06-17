<?php declare(strict_types=1);

namespace Asterios\Core\Execution;

use Asterios\Core\Env;
use Asterios\Core\Exception\EnvException;
use Asterios\Core\Exception\EnvLoadException;

final readonly class PathResolver
{
    public function __construct(private Env $env)
    {
    }

    /**
     * @param string $key
     * @return string
     * @throws EnvException
     * @throws EnvLoadException
     */
    public function resolve(string $key): string
    {
        return DIRECTORY_SEPARATOR . trim($this->env->get($key), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     * @throws EnvException
     * @throws EnvLoadException
     */
    public function operations(): string
    {
        return $this->resolve('OPERATION_PATH');
    }

    /**
     * @return string
     * @throws EnvException
     * @throws EnvLoadException
     */
    public function migrations(): string
    {
        return $this->resolve('DATABASE_MIGRATION_PATH');
    }

    /**
     * @return string
     * @throws EnvException
     * @throws EnvLoadException
     */
    public function seeders(): string
    {
        return $this->resolve('DATABASE_SEEDER_PATH');
    }

    /**
     * @return string
     * @throws EnvException
     * @throws EnvLoadException
     */
    public function commands(): string
    {
        return $this->resolve('CLI_COMMAND_PATH');
    }

    /**
     * @param string $envFile
     * @return self
     */
    public static function fromEnvFile(string $envFile): self
    {
        return new self(new Env($envFile));
    }
}
