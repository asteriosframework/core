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
     * @inheritdoc
     */
    public function int(mixed $value): int
    {
        return (int)$value;
    }

    /**
     * @inheritdoc
     */
    public function string(mixed $value): string
    {
        return (string)$value;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function double(mixed $value): float
    {
        return (float)$value;
    }

    /**
     * @inheritdoc
     */
    public function float(mixed $value): float
    {
        return (float)$value;
    }

    /**
     * @inheritdoc
     */
    public function object(array $value): object
    {
        return (object)$value;
    }
}
