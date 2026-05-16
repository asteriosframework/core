<?php declare(strict_types=1);

namespace Asterios\Core\Math;

use Asterios\Core\Config\MathConfig;

final readonly class Math
{
    public function __construct(
        private MathConfig $config = new MathConfig(),
    ) {
    }

    public static function create(?MathConfig $config = null): self
    {
        return new self($config ?? new MathConfig());
    }

    public function tax(): TaxCalculator
    {
        return new TaxCalculator($this->config);
    }

    public function geometry(): GeometryCalculator
    {
        return new GeometryCalculator($this->config);
    }

    public function units(): UnitConverter
    {
        return new UnitConverter($this->config);
    }

    public function temperature(): TemperatureConverter
    {
        return new TemperatureConverter($this->config);
    }
}
