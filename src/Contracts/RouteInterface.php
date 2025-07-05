<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

interface RouteInterface
{
    /**
     * @param string $uri
     * @param string|array $action
     * @param array $options
     * @return void
     */
    public static function get(string $uri, string|array $action, array $options = []): void;

    /**
     * @param string $uri
     * @param string|array $action
     * @param array $options
     * @return void
     */
    public static function post(string $uri, string|array $action, array $options = []): void;

    /**
     * @param string $uri
     * @param string|array $action
     * @param array $options
     * @return void
     */
    public static function put(string $uri, string|array $action, array $options = []): void;

    /**
     * @param string $uri
     * @param string|array $action
     * @param array $options
     * @return void
     */
    public static function delete(string $uri, string|array $action, array $options = []): void;

    /**
     * @param array|string $methods
     * @param string $uri
     * @param string|array $action
     * @param array $options
     * @return void
     */
    public static function match(array|string $methods, string $uri, string|array $action, array $options = []): void;

    /**
     * @param string $name
     * @param string $controller
     * @param array $options
     * @return void
     */
    public static function resource(string $name, string $controller, array $options = []): void;

    /**
     * @param array $attributes
     * @param callable $callback
     * @return void
     */
    public static function group(array $attributes, callable $callback): void;
}
