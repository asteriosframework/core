<?php declare(strict_types=1);

namespace Asterios\Core\Operation;

use Asterios\Core\Asterios;
use Asterios\Core\Config;
use Asterios\Core\Contracts\Operation\OperationInterface;
use Asterios\Core\Env;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Execution\AbstractFileExecutor;
use Asterios\Core\Execution\PathResolver;
use Asterios\Core\Logger;

class Operation extends AbstractFileExecutor
{
    private bool $force = false;

    private OperationStatusRepository $statusRepository;

    public function __construct(string $envFile = '.env')
    {
        $this->envFile = Asterios::getBasePath() . DIRECTORY_SEPARATOR . $envFile;

        Config::set_config_path(
            Asterios::getBasePath() . DIRECTORY_SEPARATOR . 'config'
        );

        if (null === $this->env)
        {
            $this->env = new Env($this->envFile);
        }

        $this->pathResolver = new PathResolver(
            $this->env
        );

        $this->statusRepository = new OperationStatusRepository();
    }

    public function force(): void
    {
        $this->force = true;
    }

    public function execute(): bool
    {
        $operationPath = $this->getOperationsPath();

        if (!$operationPath || !is_dir($operationPath))
        {
            $this->logError(
                'Could not load operations path: ' . $operationPath
            );

            return false;
        }

        try
        {
            $this->statusRepository->ensureTableExists();
        }
        catch (ConfigLoadException $e)
        {
            $this->logError(
                'Could not load config file: ' . $e->getMessage()
            );

            return false;
        }

        foreach ($this->getPhpFiles($operationPath) as $file)
        {
            $operationName = basename($file, '.php');

            try
            {
                if (
                    !$this->force
                    && $this->statusRepository->hasRun($operationName)
                ) {
                    Logger::forge()
                        ->info(
                            'Skipping already executed operation: '
                            . $operationName
                        );

                    $this->messages[][$operationName] = 'skipped';

                    continue;
                }

                $operation = $this->loadPhpFile($file);

                if (!$operation instanceof OperationInterface)
                {
                    throw new \RuntimeException(
                        'Operation must implement OperationInterface: '
                        . basename($file)
                    );
                }

                $operation->run();

                $this->statusRepository->ensureMarkedAsRun($operationName);

                Logger::forge()
                    ->info(
                        'Run operation: '
                        . basename($file)
                    );

                $this->messages[][$operationName] = 'done';
            }
            catch (\Throwable $e)
            {
                $this->messages[][$operationName] = 'failed';

                $this->logError(
                    'Operation failed: '
                    . basename($file)
                    . ' - '
                    . $e->getMessage()
                );

                return false;
            }
        }

        return true;
    }

    public function getOperationsPath(): ?string
    {
        try
        {
            return Asterios::getBasePath() . $this->pathResolver->operations();
        }
        catch (\Throwable)
        {
            return null;
        }
    }

    public function getRanOperations(): array
    {
        try
        {
            $this->statusRepository->ensureTableExists();

            return $this->statusRepository->getRanOperations();
        }
        catch (ConfigLoadException)
        {
            return [];
        }
    }

    public function getAllOperationFiles(): array
    {
        $operationPath = $this->getOperationsPath();

        if (!$operationPath)
        {
            return [];
        }

        return $this->getPhpFiles($operationPath);
    }

    public function hasExecuted(
        array $operations,
        string $operationName
    ): bool {
        foreach ($operations as $operation)
        {
            if (
                ($operation['operation'] ?? '')
                === $operationName
            ) {
                return true;
            }
        }

        return false;
    }

    public function ensureMarkedAsRun(
        string $operationName
    ): void {
        if (!$this->hasRun($operationName))
        {
            $this->markAsRun($operationName);
        }
    }
}
