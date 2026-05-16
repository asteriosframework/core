<?php declare(strict_types=1);

namespace Asterios\Core\Math;

use Asterios\Core\Config\MathConfig;
use Asterios\Core\Contracts\Math\GeometryCalculatorInterface;

class GeometryCalculator implements GeometryCalculatorInterface
{
    public function __construct(private readonly MathConfig $config = new MathConfig())
    {
    }

    /**
     * @inheritDoc
     */
    public function squareMetres(float $length, float $width, ?int $precision = null): float
    {
        $precision = $precision ?? $this->config->defaultPrecision;

        return round($length * $width, $precision);
    }

    /**
     * @inheritDoc
     */
    public function cubicMetres(float $length, float $width, float $height, ?int $precision = null): float
    {
        $precision = $precision ?? $this->config->defaultPrecision;

        return round($length * $width * $height, $precision);
    }

    /**
     * @inheritDoc
     */
    public function cubicMetresToLitres(float $cubicMetres, ?int $precision = null): float
    {
        $precision = $precision ?? $this->config->defaultPrecision;

        return round($cubicMetres * 1000, $precision);
    }
}
