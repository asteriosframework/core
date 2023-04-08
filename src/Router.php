<?php
declare(strict_types=1);

namespace Asterios\Core;

class Router
{
    /**
     * @var array The route patterns and their handling functions
     */
    private $after_routes = [];

    /**
     * @var array The before middleware route patterns and their handling functions
     */
    private $before_routes = [];

    /**
     * @var object|callable The function to be executed when no route has been matched
     */
    protected $not_found_callback;

    /**
     * @var string Current base route, used for (sub)route mounting
     */
    private $base_route = '';

    /**
     * @var string The Request Method that needs to be handled
     */
    private $requested_method = '';

    /**
     * @var string The Server Base Path for Router Execution
     */
    private $server_base_path;

    /**
     * @var string Default Controllers Namespace
     */
    private $namespace = '';

    /**
     * @var array
     */
    protected $routes;

    /**
     * @var string
     */
    protected $config_name;

    /**
     * Router constructor.
     * @param string $config_name
     * @throws Exception\ConfigLoadException
     */
    public function __construct(string $config_name = 'routes')
    {
        $this->set_config_name($config_name);
        $this->set_routes($this->routes_config());
        $this->prepare_routes();
    }

    /**
     * Store a before middleware route and a handling function to be executed when accessed using one of the specified methods.
     *
     * @param string $methods Allowed methods, | delimited
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function before(string $methods, string $pattern, $fn): void
    {
        $pattern = $this->base_route . '/' . trim($pattern, '/');
        $pattern = $this->base_route ? rtrim($pattern, '/') : $pattern;

        foreach (explode('|', $methods) as $method)
        {
            $this->before_routes[$method][] = [
                'pattern' => $pattern,
                'fn' => $fn,
            ];
        }
    }

    /**
     * Store a route and a handling function to be executed when accessed using one of the specified methods.
     *
     * @param string $methods Allowed methods, | delimited
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function match(string $methods, string $pattern, $fn): void
    {
        $pattern = $this->base_route . '/' . trim($pattern, '/');
        $pattern = $this->base_route ? rtrim($pattern, '/') : $pattern;

        foreach (explode('|', $methods) as $method)
        {
            $this->after_routes[$method][] = [
                'pattern' => $pattern,
                'fn' => $fn,
            ];
        }
    }

    /**
     * Shorthand for a route accessed using any method.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function all(string $pattern, $fn): void
    {
        $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using GET.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function get(string $pattern, $fn): void
    {

        $this->match('GET', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using POST.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function post(string $pattern, $fn): void
    {
        $this->match('POST', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using PATCH.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function patch(string $pattern, $fn): void
    {
        $this->match('PATCH', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using DELETE.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function delete(string $pattern, $fn): void
    {
        $this->match('DELETE', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using PUT.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function put(string $pattern, $fn): void
    {
        $this->match('PUT', $pattern, $fn);
    }

    /**
     * Mounts a collection of callbacks onto a base route.
     *
     * @param string $base_route The route sub pattern to mount the callbacks on
     * @param callable $fn The callback method
     */
    public function mount(string $base_route, callable $fn): void
    {
        $curl_base_route = $this->base_route;

        $this->base_route .= $base_route;

        $fn();

        $this->base_route = $curl_base_route;
    }

    /**
     * Shorthand for a route accessed using OPTIONS.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function options(string $pattern, $fn): void
    {
        $this->match('OPTIONS', $pattern, $fn);
    }

    /**
     * Get all request headers.
     *
     * @return array The request headers
     */
    public function get_request_headers()
    {
        $headers = [];

        if (function_exists('getallheaders'))
        {
            $headers = getallheaders();

            if (false !== $headers)
            {
                return $headers;
            }
        }

        foreach ($_SERVER as $name => $value)
        {
            if ($name === 'CONTENT_TYPE' || $name === 'CONTENT_LENGTH' || (strpos($name, 'HTTP_') === 0))
            {
                $headers[str_replace([' ', 'Http'], ['-', 'HTTP'], ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }

    /**
     * Get the request method used, taking overrides into account.
     *
     * @return string The Request method to handle
     */
    public function get_request_method(): string
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($_SERVER['REQUEST_METHOD'] === 'HEAD')
        {
            ob_start();
            $method = 'GET';
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            $headers = $this->get_request_headers();

            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH']))
            {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }

        return $method;
    }

    /**
     * @param string $namespace
     * @return \Asterios\Core\Router
     */
    public function set_namespace(string $namespace): Router
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Get the given Namespace before.
     *
     * @return string The given Namespace if exists
     */
    public function get_namespace(): string
    {
        return $this->namespace;
    }

    /**
     * Execute the router: Loop all defined before middlewares and routes, and execute the handling function if a match was found.
     *
     * @param object|callable $callback Function to be executed after a matching route was handled (= after router middleware)
     *
     * @return bool
     */
    public function run($callback = null): bool
    {
        $this->requested_method = $this->get_request_method();

        if (isset($this->before_routes[$this->requested_method]))
        {
            $this->handle($this->before_routes[$this->requested_method]);
        }

        $count_handled = 0;

        if (isset($this->after_routes[$this->requested_method]))
        {
            $count_handled = $this->handle($this->after_routes[$this->requested_method], true);
        }

        if (0 === $count_handled)
        {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        }
        elseif ($callback && is_callable($callback))
        {
            $callback();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'HEAD')
        {
            ob_end_clean();
        }

        return 0 !== $count_handled;
    }

    /**
     * Handle a a set of routes: if a match is found, execute the relating handling function.
     *
     * @param array $routes Collection of route patterns and their handling functions
     * @param bool $quit_after_run Does the handle function need to quit after one route was matched?
     *
     * @return int The number of routes handled
     */
    private function handle(array $routes, $quit_after_run = false): int
    {
        // Counter to keep track of the number of routes we've handled
        $count_handled = 0;

        // The current page URL
        $uri = $this->get_current_uri();

        // Loop all routes
        foreach ($routes as $route)
        {
            $route['pattern'] = preg_replace('/{([A-Za-z]*?)}/', '(\w+)', $route['pattern']);

            if (preg_match_all('#^' . $route['pattern'] . '$#', $uri, $matches, PREG_OFFSET_CAPTURE))
            {
                $matches = array_slice($matches, 1);

                $params = array_map(static function ($match, $index) use ($matches) {
                    if (isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0]))
                    {
                        return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                    }

                    return isset($match[0][0]) ? trim($match[0][0], '/') : null;
                }, $matches, array_keys($matches));

                $this->invoke($route['fn'], $params);

                ++$count_handled;

                if ($quit_after_run)
                {
                    break;
                }
            }
        }

        return $count_handled;
    }

    /**
     * @param string|callable $fn
     * @param mixed[] $params
     * @return void
     */
    private function invoke($fn, array $params = []): void
    {
        if (is_callable($fn))
        {
            call_user_func_array($fn, $params);
        }
        elseif (false !== strpos($fn, '/'))
        {
            [$controller, $method] = explode('/', $fn);

            if ('' !== $this->get_namespace())
            {
                $controller = $this->get_namespace() . '\\' . $controller;
            }

            if (class_exists($controller))
            {
                $instance = new $controller;

                $isAuth = true;

                if (method_exists($instance, 'before'))
                {
                    $isAuth = (bool)$instance->before();
                }

                if (!method_exists($instance, $method))
                {
                    $method = strtolower($this->requested_method) . '_' . $method;
                }

                if ($isAuth)
                {
                    $instance->$method(...$params);
                }
            }
            else
            {
                Logger::fatal('Controller class does not exists.', ['name' => $controller]);
            }
        }
    }

    /**
     * Define the current relative URI.
     *
     * @return string
     */
    public function get_current_uri(): string
    {
        $uri = substr($_SERVER['REQUEST_URI'], strlen($this->get_base_path()));

        if (strpos($uri, '?') !== false)
        {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Return server base Path, and define it if isn't defined.
     *
     * @return string
     */
    protected function get_base_path(): string
    {
        if (null === $this->server_base_path)
        {
            $this->server_base_path = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        }

        return $this->server_base_path;
    }

    /**
     * @return array
     * @throws Exception\ConfigLoadException
     */
    private function routes_config(): array
    {
        $new_config = [];

        $config = (array)Config::get($this->get_config_name());

        foreach ($config as $version => $routes)
        {
            foreach ($routes as $route => $route_config)
            {
                if ($this->has_string_keys($config[$version]))
                {
                    $key_version = '/' . $version . '/' . $route;
                    $new_config[$key_version] = $route_config;
                }
                else
                {
                    $new_config[$version][] = $route_config;
                }
            }
        }

        return $new_config;
    }

    /**
     * @param array $array
     * @return bool
     */
    private function has_string_keys(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * @param array $routes
     */
    public function set_routes(array $routes): void
    {
        $this->routes = $routes;
    }

    /**
     * @return array
     */
    public function get_routes(): array
    {
        return $this->routes;
    }

    protected function prepare_routes(): void
    {
        foreach ($this->routes as $route => $route_name)
        {
            foreach ($route_name as $value)
            {
                $method = strtolower($value[0]);
                $this->$method($route, $value[1]);
            }
        }
    }

    /**
     * @param string $config_name
     */
    public function set_config_name(string $config_name): void
    {
        $this->config_name = $config_name;
    }

    /**
     * @return string
     */
    public function get_config_name(): string
    {
        return $this->config_name;
    }
}
