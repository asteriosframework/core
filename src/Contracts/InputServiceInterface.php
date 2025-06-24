<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

interface InputServiceInterface
{
    /**
     * @return string
     */
    public function ip(): string;

    /**
     * @return string
     */
    public function realIp(): string;

    /**
     * @return bool
     */
    public function isAjax(): bool;

    /**
     * @param string $default
     * @return string
     */
    public function referrer(string $default = ''): string;

    /**
     * @param string $default
     * @return string
     */
    public function userAgent(string $default = ''): string;

    /**
     * @param string $default
     * @return string
     */
    public function queryString(string $default = ''): string;

    /**
     * @param string $value
     * @param string $default
     * @return string
     */
    public function server(string $value, string $default = ''): string;

    /**
     * @return string
     */
    public function phpSelf(): string;

    /**
     * @param string $key
     * @param int|string|array|bool|null $default
     * @return int|string|array|bool|null
     */
    public function cookie(string $key, int|string|array|bool $default = null): int|string|array|bool|null;

    /**
     * @param string $key
     * @param int|string|array|bool|null $default
     * @return int|string|array|bool|null
     */
    public function post(string $key, int|string|array|bool $default = null): int|string|array|bool|null;

    /**
     * @param string $key
     * @param int|string|array|bool|null $default
     * @return int|string|array|bool|null
     */
    public function get(string $key, int|string|array|bool|null $default = null): int|string|array|bool|null;

    /**
     * @param string $defaultLanguage
     * @param string[]|null $allowedLanguages
     * @return string
     */
    public function getBrowserLanguage(string $defaultLanguage, array $allowedLanguages = null): string;

    /**
     * @param string $ip
     * @return string
     */
    public function maskIp(string $ip): string;
}