<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Math;

use Asterios\Core\Exception\Math\DivisionByZeroException;
use Asterios\Core\Exception\Math\InvalidArgumentMathException;

interface TaxCalculatorInterface
{
    /**
     * @param float $net
     * @param float|null $tax
     * @param int|null $precision
     * @return float
     * @throws InvalidArgumentMathException
     */
    public function gross(float $net, ?float $tax = null, ?int $precision = null): float;

    /**
     * @param float $gross
     * @param float|null $tax
     * @param int|null $precision
     * @return float
     * @throws DivisionByZeroException
     * @throws InvalidArgumentMathException
 */
    public function net(float $gross, ?float $tax = null, ?int $precision = null): float;

    /**
     * @param float $base
     * @param float $percentage
     * @param int|null $precision
     * @return float
     */
    public function percentageOf(float $base, float $percentage, ?int $precision = null): float;

    /**
     * @param float $value
     * @param float $base
     * @param int|null $precision
     * @return float
     * @throws DivisionByZeroException
     */
    public function percentage(float $value, float $base, ?int $precision = null): float;
}
