<?php

declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Contracts\InputServiceInterface;

class InputService implements InputServiceInterface
{
    /**
     * @inheritDoc
     */
    public function ip(): string
    {
        return $this->server('REMOTE_ADDR');
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function isAjax(): bool
    {
        return (null !== $this->server('HTTP_X_REQUESTED_WITH')) && strtolower($this->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    /**
     * @inheritDoc
     */
    public function referrer(string $default = ''): string
    {
        return $this->server('HTTP_REFERER', $default);
    }

    /**
     * @inheritDoc
     */
    public function userAgent(string $default = ''): string
    {
        return $this->server('HTTP_USER_AGENT', $default);
    }

    /**
     * @inheritDoc
     */
    public function queryString(string $default = ''): string
    {
        return $this->server('QUERY_STRING', $default);
    }

    /**
     * @inheritDoc
     */
    public function server(string $value, string $default = ''): string
    {
        return (!empty($_SERVER[$value])) ? $_SERVER[$value] : $default;
    }

    /**
     * @inheritDoc
     */
    public function phpSelf(): string
    {
        return $this->server('PHP_SELF');
    }

    /**
     * @inheritDoc
     */
    public function cookie(string $key, int|string|array|bool $default = null): int|string|array|bool|null
    {
        return $_COOKIE[$key] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function post(string $key, int|string|array|bool $default = null): int|string|array|bool|null
    {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    /**
     * @inheritDoc
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

    /**
     * @inheritDoc
     */
    public function getBrowserLanguage(string $defaultLanguage, array $allowedLanguages = null): string
    {
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';

        if (preg_match_all('/([a-z]{2})[-_a-zA-Z]*\s*(;\s*q=\s*[0-9.]+)?/', $acceptLang, $matches))
        {
            foreach ($matches[1] as $lang)
            {
                if (in_array($lang, $allowedLanguages, true))
                {
                    return $lang;
                }
            }
        }

        return $defaultLanguage;
    }

    /**
     * @inheritDoc
     */
    public function maskIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        {
            $parts = explode('.', $ip);
            if (count($parts) === 4)
            {
                return $parts[0] . '.' . $parts[1] . '.***.***';
            }
        }
        elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
        {
            $parts = explode(':', $ip);
            $masked = array_slice($parts, 0, 2);
            while (count($masked) < 8)
            {
                $masked[] = '****';
            }
            return implode(':', $masked);
        }

        return '***.***.***.***';
    }
}
