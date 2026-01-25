<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Contracts\CastInterface;

class Cast implements CastInterface
{
    public static function forge(): self
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function int(mixed $value): int
    {
        return (int)$value;
    }

    /**
     * @inheritDoc
     */
    public function string(mixed $value): string
    {
        return (string)$value;
    }

    /**
     * @inheritDoc
     */
    public function bool(mixed $value): bool
    {
        return match ($value)
        {
            true, 'true', '1', 1 => true,
            default => false
        };
    }

    /**
     * @inheritDoc
     */
    public function double(mixed $value): float
    {
        return (float)$value;
    }

    /**
     * @inheritDoc
     */
    public function float(mixed $value): float
    {
        return (float)$value;
    }

    /**
     * @inheritDoc
     */
    public function object(array $value): object
    {
        return (object)$value;
    }

    /**
     * @inheritDoc
     */
    public function stringToArray(string $value, string $separator = ','): array
    {
        return explode($separator, $value);
    }

    /**
     * @inheritDoc
     */
    public function arrayToString(array $value, string $separator = ','): string
    {
        return implode($separator, $value);
    }
}
