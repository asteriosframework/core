<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces\Cli;

interface CommandRegistryInterface
{
    /**
     * @return array
     * @throws \ReflectionException
     */
    public function all(): array;

    /**
     * @param string $name
     * @return array|null
     * @throws \ReflectionException
     */
    public function findByNameOrAlias(string $name): ?array;
}