<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Math;

use Asterios\Core\Enum\Math\TemperatureScale;

interface TemperatureConverterInterface
{
    /**
     * @param float $value
     * @param TemperatureScale $source
     * @param TemperatureScale $target
     * @param int|null $precision
     * @return float
     */
    public function convert(float $value, TemperatureScale $source, TemperatureScale $target, ?int $precision = null): float;
}