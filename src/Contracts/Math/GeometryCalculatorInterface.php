<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Math;

interface GeometryCalculatorInterface
{
    /**
     * @param float $length
     * @param float $width
     * @param int|null $precision
     * @return float
     */
    public function squareMetres(float $length, float $width, ?int $precision = null): float;

    /**
     * @param float $length
     * @param float $width
     * @param float $height
     * @param int|null $precision
     * @return float
     */
    public function cubicMetres(float $length, float $width, float $height, ?int $precision = null): float;

    /**
     * @param float $cubicMetres
     * @param int|null $precision
     * @return float
     */
    public function cubicMetresToLitres(float $cubicMetres, ?int $precision = null): float;
}