<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces;

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
}