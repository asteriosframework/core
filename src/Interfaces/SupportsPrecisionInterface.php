<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces;

interface SupportsPrecisionInterface
{
    /**
     * @param int $value
     * @return static
     */
    public function precision(int $value): static;
}