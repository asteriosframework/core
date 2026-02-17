<?php

declare(strict_types=1);

namespace Asterios\Core\Support;

use Asterios\Core\Contracts\RouteInterface;

class Route implements RouteInterface
{
    public static array $routes = [];
    public static array $groupStack = [];

    /**
     * @inheritDoc
     */
    public static function get(string $uri, string|array $action, array $options = []): void
    {
        self::addRoute('GET', $uri, $action, $options);
    }

    /**
     * @inheritDoc
     */
    public static function post(string $uri, string|array $action, array $options = []): void
    {
        self::addRoute('POST', $uri, $action, $options);
    }

    /**
     * @inheritDoc
     */
    public static function put(string $uri, string|array $action, array $options = []): void
    {
        self::addRoute('PUT', $uri, $action, $options);
    }

    /**
     * @inheritDoc
     */
    public static function patch(string $uri, string|array $action, array $options = []): void
    {
        self::addRoute('PATCH', $uri, $action, $options);
    }

    /**
     * @inheritDoc
     */
    public static function delete(string $uri, string|array $action, array $options = []): void
    {
        self::addRoute('DELETE', $uri, $action, $options);
    }

    /**
     * @inheritDoc
     */
    public static function options(string $uri, string|array $action, array $options = []): void
    {
        self::addRoute('OPTIONS', $uri, $action, $options);
    }

    /**
     * @inheritDoc
     */
    public static function match(array|string $methods, string $uri, string|array $action, array $options = []): void
    {
        $methods = is_array($methods) ? $methods : [$methods];

        foreach ($methods as $method)
        {
            self::addRoute(strtoupper($method), $uri, $action, $options);
        }
    }

    /**
     * @inheritDoc
     */
    public static function resource(string $name, string $controller, array $options = []): void
    {
        $allRoutes = [
            'index' => ['GET', $name, 'index'],
            'create' => ['GET', $name.'/create', 'create'],
            'store' => ['POST', $name, 'store'],
            'show' => ['GET', $name.'/{id}', 'show'],
            'edit' => ['GET', $name.'/{id}/edit', 'edit'],
            'update' => ['PUT', $name.'/{id}', 'update'],
            'destroy' => ['DELETE', $name.'/{id}', 'destroy'],
        ];

        if (isset($options['only']))
        {
            $only = (array)$options['only'];
            $allRoutes = array_filter($allRoutes, static fn ($key) => in_array($key, $only, true), ARRAY_FILTER_USE_KEY);
        }
        elseif (isset($options['except']))
        {
            $except = (array)$options['except'];
            $allRoutes = array_filter($allRoutes, static fn ($key) => !in_array($key, $except, true), ARRAY_FILTER_USE_KEY);
        }

        foreach ($allRoutes as $route)
        {
            [$method, $uri, $actionMethod] = $route;
            self::addRoute($method, $uri, [$controller, $actionMethod], $options);
        }
    }

    /**
     * @inheritDoc
     */
    public static function group(array $attributes, callable $callback): void
    {
        self::$groupStack[] = $attributes;
        $callback();
        array_pop(self::$groupStack);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string|array $action
     * @param array $options
     * @return void
     */
    private static function addRoute(string $method, string $uri, string|array $action, array $options = []): void
    {
        $middlewaresArrays = [];
        $prefixParts = [];

        foreach (self::$groupStack as $group)
        {
            if (!empty($group['middleware']))
            {
                $middlewaresArrays[] = (array)$group['middleware'];
            }

            if (!empty($group['prefix']))
            {
                $prefixParts[] = trim($group['prefix'], '/');
            }
        }

        $prefix = $prefixParts ? implode('/', $prefixParts) . '/' : '';

        if (!empty($options['middleware']))
        {
            $middlewaresArrays[] = (array)$options['middleware'];
        }

        $middlewares = [];
        if (!empty($middlewaresArrays))
        {
            $middlewares = array_merge(...$middlewaresArrays);
        }

        $uri = $prefix . ltrim($uri, '/');

        self::$routes[] = [
            'method' => strtoupper($method),
            'route' => '/' . trim($uri, '/'),
            'action' => $action,
            'middlewares' => $middlewares,
        ];
    }
}
