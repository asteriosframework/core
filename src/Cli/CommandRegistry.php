<?php declare(strict_types=1);

namespace Asterios\Core\Cli;

use Asterios\Core\Cli\Attributes\Command;
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

        $commands = [];

        $files = $this->getAllPhpFiles(__DIR__ . '/Commands');

        foreach ($files as $file)
        {
            require_once $file;
        }

        foreach (get_declared_classes() as $class)
        {
            if (!is_subclass_of($class, CommandInterface::class))
                continue;

            $ref = new ReflectionClass($class);
            $attrs = $ref->getAttributes(Command::class);

            if (empty($attrs))
            {
                continue;
            }

            /** @var Command $attr */
            $attr = $attrs[0]->newInstance();

            $commands[] = [
                'name' => $attr->name,
                'description' => $attr->description,
                'group' => $attr->group,
                'aliases' => $attr->aliases,
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
            if ($command['name'] === $name || in_array($name, $command['aliases'] ?? [], true))
            {
                return $command;
            }
        }

        return null;
    }

    /**
     * @param string $dir
     * @return array
     */
    protected function getAllPhpFiles(string $dir): array
    {
        $files = [];
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

        foreach ($rii as $file)
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
