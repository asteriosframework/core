<?php declare(strict_types=1);

namespace Asterios\Core\Cli;

use Asterios\Core\Asterios;
use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Config;
use Asterios\Core\Contracts\Cli\CommandRegistryInterface;
use Asterios\Core\Contracts\CommandInterface;
use ReflectionClass;

class CommandRegistry implements CommandRegistryInterface
{
    private ?array $discovered = null;

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        if ($this->discovered !== null)
        {
            return $this->discovered;
        }

        $this->loadCommandClasses();

        $commands = [];
        $registeredNames = [];

        foreach (get_declared_classes() as $class)
        {
            if (!is_subclass_of($class, CommandInterface::class))
            {
                continue;
            }

            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(Command::class);

            if (empty($attributes))
            {
                continue;
            }

            /** @var Command $attribute */
            $attribute = $attributes[0]->newInstance();

            if (isset($registeredNames[$attribute->name]))
            {
                continue;
            }

            $registeredNames[$attribute->name] = true;

            $commands[] = [
                'name' => $attribute->name,
                'description' => $attribute->description,
                'group' => $attribute->group,
                'aliases' => $attribute->aliases,
                'class' => $class,
            ];
        }

        return $this->discovered = $commands;
    }

    /**
     * @inheritDoc
     */
    public function findByNameOrAlias(string $name): ?array
    {
        foreach ($this->all() as $command)
        {
            if ($command['name'] === $name)
            {
                return $command;
            }

            if (in_array($name, $command['aliases'] ?? [], true))
            {
                return $command;
            }
        }

        return null;
    }

    /**
     * @return void
     */
    private function loadCommandClasses(): void
    {
        foreach ($this->getCommandDirectories() as $directory)
        {
            if (!is_dir($directory))
            {
                continue;
            }

            foreach ($this->getAllPhpFiles($directory) as $file)
            {
                require_once $file;
            }
        }
    }

    /**
     * @return array
     */
    private function getCommandDirectories(): array
    {
        $directories = [
            __DIR__ . '/Commands',
        ];

        $configuredPath = $this->getConfiguredCommandPath();

        if (!empty($configuredPath))
        {
            $directories[] = Asterios::getBasePath(
                ltrim($configuredPath, DIRECTORY_SEPARATOR)
            );
        }

        return array_unique($directories);
    }

    private function getConfiguredCommandPath(): string
    {
        try
        {
            return Config::get('cli', 'command_path') ?? 'app/Cli/Commands';
        }
        catch (\Throwable)
        {
            return 'app/Cli/Commands';
        }
    }

    /**
     * @param string $directory
     * @return array
     */
    private function getAllPhpFiles(string $directory): array
    {
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file)
        {
            if ($file->isDir())
            {
                continue;
            }

            if ($file->getExtension() !== 'php')
            {
                continue;
            }

            $files[] = $file->getRealPath();
        }

        return $files;
    }
}
