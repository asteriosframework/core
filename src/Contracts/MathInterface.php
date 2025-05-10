<?php declare(strict_types=1);

namespace Asterios\Core\Contracts;

use Asterios\Core\Enum\MathEnum;

interface MathInterface
{
    /**
     * @param float $brutto
     * @param int $precision
     * @param float|null $tax
     * @return float
     */
    public function netto(float $brutto, int $precision = 2, ?float $tax = null): float;

    /**
     * @param float $netto
     * @param int $precision
     * @param float|null $tax
     * @return float
     */
    public function brutto(float $netto, int $precision = 2, ?float $tax = null): float;

    /**
     * @param float $netto
     * @param float|null $percentage
     * @param int $precision
     * @return float
     */
    public function percentageValue(float $netto, ?float $percentage = null, int $precision = 2): float;

    /**
     * @param float $percentageValue
     * @param float|null $netto
     * @param int $precision
     * @return float
     */
    public function percentage(float $percentageValue, float $netto = null, int $precision = 2): float;

    /**
     * @param float $length
     * @param float $width
     * @param int $precision
     * @return float
     */
    public function squareMetre(float $length, float $width, int $precision = 2): float;

    /**
     * @param float $length
     * @param float $width
     * @param float $height
     * @param int $precision
     * @return float
     */
    public function cubicMetre(float $length, float $width, float $height, int $precision = 2): float;

    /**
     * @param float $cubicMetre
     * @return float
     */
    public function cubicInLitre(float $cubicMetre): float;

    /**
     * @param float $miles
     * @param int $hours
     * @return float
     */
    public function mph(float $miles, int $hours): float;

    /**
     * @param float $km
     * @param int $hours
     * @return float
     */
    public function kmh(float $km, int $hours): float;

    /**
     * @param float $km
     * @param int $precision
     * @return float
     */
    public function kmInMiles(float $km, int $precision = 2): float;

    /**
     * @param float $miles
     * @param int $precision
     * @return float
     */
    public function milesInKm(float $miles, int $precision = 2): float;

    /**
     * @param float $value
     * @param MathEnum $sourceScale
     * @param MathEnum $targetScale
     * @param int $precision
     * @return float
     */
    public function temperature(float $value, MathEnum $sourceScale, MathEnum $targetScale, int $precision = 2): float;

    /**
     * @param float $value
     * @param MathEnum $sourceScale
     * @param int $precision
     * @return float
     */
    public function toCelsius(float $value, MathEnum $sourceScale, int $precision = 2): float;

    /**
     * @param float $value
     * @param MathEnum $sourceScale
     * @param int $precision
     * @return float
     */
    public function toKelvin(float $value, MathEnum $sourceScale, int $precision = 2): float;

    /**
     * @param float $value
     * @param MathEnum $sourceScale
     * @param int $precision
     * @return float
     */
    public function toRankine(float $value, MathEnum $sourceScale, int $precision = 2): float;

    /**
     * @param float $value
     * @param MathEnum $sourceScale
     * @param int $precision
     * @return float
     */
    public function toFahrenheit(float $value, MathEnum $sourceScale, int $precision = 2): float;

    /**
     * @param float $value
     * @param MathEnum $sourceScale
     * @param int $precision
     * @return float
     */
    public function toReaumur(float $value, MathEnum $sourceScale, int $precision = 2): float;
}