<?php declare(strict_types=1);

namespace Asterios\Core;

class InputService
{
    public function ip(): string
    {
        return $this->server('REMOTE_ADDR');
    }

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

    public function isAjax(): bool
    {
        return (null !== $this->server('HTTP_X_REQUESTED_WITH')) && strtolower($this->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    public function referrer(string $default = ''): string
    {
        return $this->server('HTTP_REFERER', $default);
    }

    public function userAgent(string $default = ''): string
    {
        return $this->server('HTTP_USER_AGENT', $default);
    }

    public function queryString(string $default = ''): string
    {
        return $this->server('QUERY_STRING', $default);
    }

    public function server(string $value, string $default = ''): string
    {
        return (!empty($_SERVER[$value])) ? $_SERVER[$value] : $default;
    }

    public function phpSelf(): string
    {
        return $this->server('PHP_SELF');
    }

    /**
     * @param string $key
     * @param int|string|array|bool|null $default
     * @return int|string|array|bool|null
     */
    public function cookie(string $key, int|string|array|bool $default = null): int|string|array|bool|null
    {
        return $_COOKIE[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param int|string|array|bool|null $default
     * @return int|string|array|bool|null
     */
    public function post(string $key, int|string|array|bool $default = null): int|string|array|bool|null
    {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    /**
     * @param string $key
     * @param int|string|array|bool|null $default
     * @return int|string|array|bool|null
     */
    public function get(string $key, int|string|array|bool|null $default = null): int|string|array|bool|null
    {
        if (isset($_GET[$key]))
        {
            if (is_array($_GET[$key]))
            {
                return $_GET[$key];
            }

            return trim($_GET[$key]);
        }

        return $default;
    }
}