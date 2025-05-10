<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

interface CastInterface
{
    /**
     * @param mixed $value
     * @return int
     */
    public function int(mixed $value): int;

    /**
     * @param mixed $value
     * @return string
     */
    public function string(mixed $value): string;

    /**
     * @param mixed $value
     * @return bool
     */
    public function bool(mixed $value): bool;

    /**
     * @param mixed $value
     * @return float
     */
    public function double(mixed $value): float;

    /**
     * @param mixed $value
     * @return float
     */
    public function float(mixed $value): float;

    /**
     * @param array $value
     * @return object
     */
    public function object(array $value): object;

}