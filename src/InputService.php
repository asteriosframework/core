<?php

declare(strict_types=1);

namespace Asterios\Core;

class InputService
{
    /**
     * @return string
     */
    public function ip(): string
    {
        return $this->server('REMOTE_ADDR');
    }

    /**
     * @return string
     */
    public function realIp(): string
    {
        if (!empty($this->server('HTTP_CLIENT_IP')))
        {
            $ip = $this->server('HTTP_CLIENT_IP');
        }
        elseif (!empty($this->server('HTTP_X_FORWARDED_FOR')))
        {
            $ip = $this->server('HTTP_X_FORWARDED_FOR');
        }
        else
        {
            $ip = $this->server('REMOTE_ADDR');
        }

        return $ip;
    }

    /**
     * @return bool
     */
    public function isAjax(): bool
    {
        return (strtolower($this->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');
    }

    /**
     * @param string $default
     * @return string
     */
    public function referrer(string $default = ''): string
    {
        return $this->server('HTTP_REFERER', $default);
    }

    /**
     * @param string $default
     * @return string
     */
    public function userAgent(string $default = ''): string
    {
        return $this->server('HTTP_USER_AGENT', $default);
    }

    /**
     * @param string $default
     * @return string
     */
    public function queryString(string $default = ''): string
    {
        return $this->server('QUERY_STRING', $default);
    }

    /**
     * @param string $value
     * @param string $default
     * @return string
     */
    public function server(string $value, string $default = ''): string
    {
        return (!empty($_SERVER[$value])) ? $_SERVER[$value] : $default;
    }

    /**
     * @return string
     */
    public function phpSelf(): string
    {
        return $this->server('PHP_SELF');
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        return $_COOKIE[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($_GET[$key]))
        {
            return is_array($_GET[$key]) ? $_GET[$key] : trim($_GET[$key]);
        }

        return $default;
    }

    /**
     * PUT/PATCH/DELETE Body
     * @return array
     * @throws \JsonException
     */
    public function body(): array
    {
        $raw = file_get_contents('php://input');

        if (empty($raw))
        {
            return [];
        }

        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        return (json_last_error() === JSON_ERROR_NONE) ? $data : [];
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     * @throws \JsonException
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $body = $this->body();

        return $body[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     * @throws \JsonException
     */
    public function request(string $key, mixed $default = null): mixed
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        return match ($method)
        {
            'GET'    => $this->get($key, $default),
            'POST'   => $this->post($key, $default),
            'PUT',
            'PATCH',
            'DELETE' => $this->input($key, $default),
            default  => $default,
        };
    }

    /**
     * @return array
     * @throws \JsonException
     */
    public function all(): array
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        return match ($method)
        {
            'GET'    => $_GET,
            'POST'   => $_POST,
            'PUT',
            'PATCH',
            'DELETE' => $this->body(),
            default  => [],
        };
    }
}
