<?php declare(strict_types=1);

namespace Asterios\Core\Http;

use JsonException;

class ServerRequest
{
    private ?string $body = null;

    /**
     * Returns the HTTP request method.
     */
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Returns whether the current request uses the given HTTP method.
     */
    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    public function isPatch(): bool
    {
        return $this->isMethod('PATCH');
    }

    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    public function isOptions(): bool
    {
        return $this->isMethod('OPTIONS');
    }

    public function isHead(): bool
    {
        return $this->isMethod('HEAD');
    }

    public function isQuery(): bool
    {
        return $this->isMethod('QUERY');
    }

    /**
     * Returns the raw request body.
     */
    public function body(): string
    {
        if ($this->body === null)
        {
            $this->body = file_get_contents('php://input') ?: '';
        }

        return $this->body;
    }

    /**
     * Returns the decoded JSON body.
     *
     * @throws JsonException
     */
    public function json(bool $associative = true): array|object|null
    {
        $body = $this->body();

        if ($body === '')
        {
            return $associative ? [] : null;
        }

        return json_decode(
            $body,
            $associative,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * Returns all query parameters or a single value.
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null)
        {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    /**
     * Returns all POST parameters or a single value.
     *
     * Mainly for backwards compatibility with HTML forms.
     */
    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null)
        {
            return $_POST;
        }

        return $_POST[$key] ?? $default;
    }

    /**
     * Returns all uploaded files or a single file.
     */
    public function files(?string $key = null): mixed
    {
        if ($key === null)
        {
            return $_FILES;
        }

        return $_FILES[$key] ?? null;
    }

    /**
     * Returns all cookies or a single cookie.
     */
    public function cookie(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null)
        {
            return $_COOKIE;
        }

        return $_COOKIE[$key] ?? $default;
    }

    /**
     * Returns all server variables or a single value.
     */
    public function server(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null)
        {
            return $_SERVER;
        }

        return $_SERVER[$key] ?? $default;
    }

    /**
     * Returns all request headers.
     */
    public function headers(): array
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
                $header = str_replace('_', '-', substr($name, 5));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }

    /**
     * Returns a single request header.
     */
    public function header(string $name, mixed $default = null): mixed
    {
        $headers = $this->headers();

        foreach ($headers as $header => $value)
        {
            if (strcasecmp($header, $name) === 0)
            {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Returns the request URI.
     */
    public function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * Returns the request path without query string.
     */
    public function path(): string
    {
        return parse_url($this->uri(), PHP_URL_PATH) ?: '/';
    }

    /**
     * Returns whether the request body contains JSON.
     */
    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type', '');

        return str_contains(
            strtolower((string)$contentType),
            'application/json'
        );
    }
}
