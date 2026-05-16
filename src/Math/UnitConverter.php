<?php declare(strict_types=1);

namespace Asterios\Core\Math;

use Asterios\Core\Config\MathConfig;
use Asterios\Core\Contracts\Math\UnitConverterInterface;
use Asterios\Core\Exception\Math\DivisionByZeroException;

final readonly class UnitConverter implements UnitConverterInterface
{
    private const float KM_FACTOR = 1.609344;

    public function __construct(private MathConfig $config = new MathConfig())
    {
    }

    /**
     * @inheritDoc
     */
    public function mph(float $miles, float $hours, ?int $precision = null): float
    {
        $precision = $precision ?? $this->config->defaultPrecision;

        if ($hours === 0.0)
        {
            throw new DivisionByZeroException('Hours cannot be zero.');
        }

        return round($miles / $hours, $precision);
    }

    /**
     * @inheritDoc
     */
    public function kmh(float $kilometres, float $hours, ?int $precision = null): float
    {
        $precision = $precision ?? $this->config->defaultPrecision;

        if ($hours === 0.0)
        {
            throw new DivisionByZeroException('Hours cannot be zero.');
        }

        return round($kilometres / $hours, $precision);
    }

    /**
     * @inheritDoc
     */
    public function kmToMiles(float $kilometres, ?int $precision = null): float
    {
        $precision = $precision ?? $this->config->defaultPrecision;

        return round($kilometres / self::KM_FACTOR, $precision);
    }

    /**
     * @inheritDoc
     */
    public function milesToKm(float $miles, ?int $precision = null): float
    {
        $precision = $precision ?? $this->config->defaultPrecision;

        return round($miles * self::KM_FACTOR, $precision);
    }
}
