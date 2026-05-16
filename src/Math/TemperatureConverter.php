<?php declare(strict_types=1);

namespace Asterios\Core\Math;

use Asterios\Core\Config\MathConfig;
use Asterios\Core\Contracts\Math\TemperatureConverterInterface;
use Asterios\Core\Enum\Math\TemperatureScale;

final readonly class TemperatureConverter implements TemperatureConverterInterface
{
    public function __construct(private MathConfig $config = new MathConfig())
    {
    }

    /**
     * @inheritDoc
     */
    public function convert(float $value, TemperatureScale $source, TemperatureScale $target, ?int $precision = null): float
    {
        $precision = $precision ?? $this->config->defaultPrecision;

        if ($source === $target)
        {
            return round($value, $precision);
        }

        $kelvin = $this->toKelvin($value, $source);

        return round($this->fromKelvin($kelvin, $target), $precision);
    }

    /**
     * @param float $value
     * @param TemperatureScale $source
     * @return float
     */
    private function toKelvin(float $value, TemperatureScale $source): float
    {
        return max(0.0, match ($source)
        {
            TemperatureScale::KELVIN => $value,
            TemperatureScale::CELSIUS => $value + 273.15,
            TemperatureScale::FAHRENHEIT => ($value + 459.67) * 5 / 9,
            TemperatureScale::RANKINE => $value * 5 / 9,
            TemperatureScale::REAUMUR => ($value * 5 / 4) + 273.15,
        });
    }

    /**
     * @param float $kelvin
     * @param TemperatureScale $target
     * @return float
     */
    private function fromKelvin(float $kelvin, TemperatureScale $target): float
    {
        return match ($target)
        {
            TemperatureScale::KELVIN => $kelvin,
            TemperatureScale::CELSIUS => $kelvin - 273.15,
            TemperatureScale::FAHRENHEIT => ($kelvin * 9 / 5) - 459.67,
            TemperatureScale::RANKINE => $kelvin * 9 / 5,
            TemperatureScale::REAUMUR => ($kelvin - 273.15) * 4 / 5,
        };
    }
}