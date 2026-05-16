<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Math;

use Asterios\Core\Exception\Math\DivisionByZeroException;

interface UnitConverterInterface
{
    /**
     * @param float $miles
     * @param float $hours
     * @param int|null $precision
     * @return float
     * @throws DivisionByZeroException
     */
    public function mph(float $miles, float $hours, ?int $precision = null): float;

    /**
     * @param float $kilometres
     * @param float $hours
     * @param int|null $precision
     * @return float
     * @throws DivisionByZeroException
     */
    public function kmh(float $kilometres, float $hours, ?int $precision = null): float;

    /**
     * @param float $kilometres
     * @param int|null $precision
     * @return float
     */
    public function kmToMiles(float $kilometres, ?int $precision = null): float;

    /**
     * @param float $miles
     * @param int|null $precision
     * @return float
     */
    public function milesToKm(float $miles, ?int $precision = null): float;
}
