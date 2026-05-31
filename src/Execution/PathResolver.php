<?php declare(strict_types=1);

namespace Asterios\Core\Execution;

use Asterios\Core\Env;

final readonly class PathResolver
{
    public function __construct(
        private Env $env
    ) {
    }

    public function resolve(string $key): string
    {
        return DIRECTORY_SEPARATOR . trim($this->env->get($key), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public static function fromEnvFile(
        string $envFile
    ): self {
        return new self(
            new Env($envFile)
        );
    }
}
