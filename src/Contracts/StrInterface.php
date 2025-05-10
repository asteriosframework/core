<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

interface StrInterface
{
    /**
     * @param int|float|string|bool|null $value
     * @param string $characters
     * @return int|float|string|bool|null
     */
    public function trim($value, string $characters = " \n\r\t\v\x00");

    public function startsWith(string $string, string $start, bool $ignoreCase = false): bool;

    public function endsWith(string $string, string $end, bool $ignoreCase = false): bool;

    public function sub(string $string, int $start, ?int $length = null, ?string $encoding = 'UTF-8'): string;

    public function length(string $string, ?string $encoding = null): int;

    public function lower(string $string, ?string $encoding = null): string;

    public function upper(string $string, ?string $encoding = null): string;

    public function random(string $type = 'alnum', int $length = 16): string;

    public function isJson(string $value): bool;

    public function isXml(string $stringing): bool;

    public function isSerialized(string $value): bool;

    public function isHtml(string $value): bool;

    public function filterKeys(array $array, array $keys, bool $remove = false): array;

}