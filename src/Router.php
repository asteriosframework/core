<?php

declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Contracts\RouterInterface;
use Asterios\Core\Exception\RouterException;
use Asterios\Core\Support\Route;

class Router implements RouterInterface
{
    protected array $routes = [];
    private array $afterRoutes = [];
    private string $baseRoute = '';
    private string $requestedMethod = '';
    private string $namespace = '';
    private array $globalMiddlewares = [];
    private string $middlewareNamespace = '';

    public function __construct()
    {
        $this->setRoutes(Route::$routes);
        $this->prepareRoutes();
    }

    /**
     * @inheritDoc
     */
    public function setNamespace(string $namespace): Router
    {
        $this->namespace = rtrim($namespace, '\\');
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setMiddlewareNamespace(string $namespace): Router
    {
        $this->middlewareNamespace = rtrim($namespace, '\\');
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewareNamespace(): string
    {
        return $this->middlewareNamespace;
    }

    /**
     * @inheritDoc
     */
    public function setRoutes(array $routes): Router
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function run(callable $callback = null): bool
    {
        $this->requestedMethod = $this->getRequestMethod();

        if (!$this->executeMiddlewares($this->globalMiddlewares))
        {
            return false;
        }

        $countHandled = 0;

        if (isset($this->afterRoutes[$this->requestedMethod]))
        {
            $countHandled = $this->handle($this->afterRoutes[$this->requestedMethod], true);
        }

        if ($countHandled === 0)
        {
            http_response_code(404);
        }
        elseif ($callback)
        {
            $callback();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'HEAD')
        {
            ob_end_clean();
        }

        return $countHandled !== 0;
    }

    /**
     * @inheritDoc
     */
    public function getRequestMethod(): string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'HEAD')
        {
            ob_start();
            $method = 'GET';
        }
        elseif ($method === 'POST')
        {
            $headers = $this->getRequestHeaders();
            if (isset($headers['X-HTTP-Method-Override']) &&
                in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'], true))
            {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }

        return strtoupper($method);
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function getRequestHeaders(): array
    {
        if (function_exists('getallheaders'))
        {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value)
        {
            if (str_starts_with($name, 'HTTP_'))
            {
                $key = str_replace('_', '-', substr($name, 5));
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = substr($uri, strlen($this->getBasePath()));

        if (str_contains($uri, '?'))
        {
            $uri = strstr($uri, '?', true);
        }

        return '/' . trim($uri, '/');
    }

    /**
     * @return void
     */
    protected function prepareRoutes(): void
    {
        foreach ($this->routes as $route)
        {
            $method = strtoupper($route['method']);
            $pattern = rtrim($this->baseRoute . '/' . ltrim($route['route'], '/'), '/');

            $this->afterRoutes[$method][] = [
                'pattern' => $pattern,
                'fn' => $route['action'],
                'middlewares' => $route['middlewares'] ?? [],
            ];
        }
    }

    /**
     * @return string
     */
    protected function getBasePath(): string
    {
        return implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME'] ?? ''), 0, -1)) . '/';
    }

    /**
     * @param string|array $fn
     * @param array $params
     * @return void
     * @throws RouterException
     * @codeCoverageIgnore
     */
    private function invoke(string|array $fn, array $params = []): void
    {
        if (is_callable($fn))
        {
            call_user_func_array($fn, $params);
            return;
        }

        if (is_array($fn) && count($fn) === 2)
        {
            [$controller, $method] = $fn;
            if (!class_exists($controller))
            {
                throw new RouterException('Controller class '.$controller.' not found.');
            }

            $instance = new $controller();

            if (method_exists($instance, 'before') && !$instance->before())
            {
                return;
            }

            if (!method_exists($instance, $method))
            {
                $altMethod = strtolower($this->requestedMethod) . '_' . $method;
                if (method_exists($instance, $altMethod))
                {
                    $method = $altMethod;
                }
                else
                {
                    throw new RouterException('Method '.$method.' not found in '.$controller.'.');
                }
            }

            $instance->$method(...$params);
            return;
        }

        throw new RouterException('Invalid route action.');
    }

    /**
     * @param array $routes
     * @param bool $quitAfterRun
     * @return int
     * @throws RouterException
     * @codeCoverageIgnore
     */
    private function handle(array $routes, bool $quitAfterRun = false): int
    {
        $countHandled = 0;
        $uri = $this->getCurrentUri();

        foreach ($routes as $route)
        {
            $pattern = preg_replace('/{(\w+)}/', '([^/]+)', $route['pattern']);
            if ($pattern === null)
            {
                continue;
            }

            if (preg_match('#^' . $pattern . '$#', $uri, $matches))
            {
                $params = array_map('urldecode', array_slice($matches, 1));

                if (!$this->executeMiddlewares($route['middlewares']))
                {
                    return 1;
                }

                $this->invoke($route['fn'], $params);
                ++$countHandled;

                if ($quitAfterRun)
                {
                    break;
                }
            }
        }

        return $countHandled;
    }

    /**
     * @param array $middlewareNames
     * @return bool
     * @throws RouterException
     * @codeCoverageIgnore
     */
    private function executeMiddlewares(array $middlewareNames): bool
    {
        foreach ($middlewareNames as $name)
        {
            $class = $this->middlewareNamespace . '\\' . ucfirst($name) . 'Middleware';

            if (!class_exists($class))
            {
                throw new RouterException('Middleware ' . $class . ' not found.');
            }

            $middleware = new $class();

            if (method_exists($middleware, 'handle') && !$middleware->handle())
            {
                return false;
            }
        }

        return true;
    }
}
