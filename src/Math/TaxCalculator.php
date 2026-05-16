<?php declare(strict_types=1);

namespace Asterios\Core\Math;

use Asterios\Core\Config\MathConfig;
use Asterios\Core\Contracts\Math\TaxCalculatorInterface;
use Asterios\Core\Exception\Math\DivisionByZeroException;
use Asterios\Core\Exception\Math\InvalidArgumentMathException;

final readonly class TaxCalculator implements TaxCalculatorInterface
{
    public function __construct(private MathConfig $config = new MathConfig())
    {
    }

    /**
     * @inheritDoc
     */
    public function gross(float $net, ?float $tax = null, ?int $precision = null): float
    {
        $tax = $tax ?? $this->config->defaultTax;
        $precision = $precision ?? $this->config->defaultPrecision;

        $this->validateTax($tax);

        return round($net * (1 + ($tax / 100)), $precision);
    }

    /**
     * @inheritDoc
     */
    public function net(float $gross, ?float $tax = null, ?int $precision = null): float
    {
        $tax = $tax ?? $this->config->defaultTax;
        $precision = $precision ?? $this->config->defaultPrecision;

        $this->validateTax($tax);

        if ($tax <= -100.0)
        {
            throw new DivisionByZeroException('Tax must be greater than -100.');
        }

        return round($gross / (1 + ($tax / 100)), $precision);
    }

    /**
     * @inheritDoc
     */
    public function percentageOf(float $base, float $percentage, ?int $precision = null): float
    {
        $precision = $precision ?? $this->config->defaultPrecision;

        return round($base * ($percentage / 100), $precision);
    }

    /**
     * @inheritDoc
     */
    public function percentage(float $value, float $base, ?int $precision = null): float
    {
        $precision = $precision ?? $this->config->defaultPrecision;

        if ($base === 0.0)
        {
            throw new DivisionByZeroException('Base value cannot be zero.');
        }

        return round(($value / $base) * 100, $precision);
    }

    /**
     * @param float $tax
     * @return void
     * @throws InvalidArgumentMathException
     */
    private function validateTax(float $tax): void
    {
        if ($tax < -1000 || $tax > 1000)
        {
            throw new InvalidArgumentMathException('Tax rate out of allowed range.');
        }
    }
}
