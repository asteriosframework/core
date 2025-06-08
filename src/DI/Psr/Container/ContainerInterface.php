<?php

declare(strict_types=1);

namespace Asterios\Core\DI\Psr\Container;

use Asterios\Core\DI\Exceptions\NotFoundException;
use Asterios\Core\DI\Exceptions\ContainerException;

interface ContainerInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for **this** identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get(string $id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * @param string $key
     * @param mixed $value
     * @param array<string,mixed> $params
     * @return mixed
     */
    public function set(string $key, $value, $params = []): mixed;
}
