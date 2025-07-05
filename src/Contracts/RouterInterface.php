<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

use Asterios\Core\Exception\RouterException;
use Asterios\Core\Router;

interface RouterInterface
{
    /**
     * @param string $namespace
     * @return Router
     */
    public function setNamespace(string $namespace): Router;

    /**
     * @return string
     */
    public function getNamespace(): string;

    /**
     * @param string $namespace
     * @return Router
     */
    public function setMiddlewareNamespace(string $namespace): Router;

    /**
     * @return string
     */
    public function getMiddlewareNamespace(): string;

    /**
     * @param array $routes
     * @return Router
     */
    public function setRoutes(array $routes): Router;

    /**
     * @param callable|null $callback
     * @return bool
     * @throws RouterException
     */
    public function run(callable $callback = null): bool;

    /**
     * @return string
     */
    public function getRequestMethod(): string;

    /**
     * @return array
     */
    public function getRequestHeaders(): array;

    /**
     * @return string
     */
    public function getCurrentUri(): string;
}
