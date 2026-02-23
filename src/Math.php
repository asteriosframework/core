<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Contracts\MathInterface;
use Asterios\Core\Dto\MathDto;
use Asterios\Core\Enum\MathEnum;

class Math implements MathInterface
{
    protected MathDto $dto;

    public function __construct(MathDto $dto)
    {
        $this->dto = $dto;
    }

    public static function forge(MathDto $dto): self
    {
        return new self($dto);
    }

    /**
     * @inheritdoc
     */
    public function netto(float $brutto, int $precision = 2, ?float $tax = null): float
    {
        if (null === $tax)
        {
            $tax = $this->dto->getTax();
        }

        return $this->getFloatWithPrecision($brutto / (1 + ($tax / 100)), $precision);
    }

    /**
     * @inheritdoc
     */
    public function brutto(float $netto, int $precision = 2, ?float $tax = null): float
    {
        if (null === $tax)
        {
            $tax = $this->dto->getTax();
        }

        return $this->getFloatWithPrecision($netto * (1 + ($tax / 100)), $precision);
    }

    /**
     * @inheritdoc
     */
    public function percentageValue(float $netto, ?float $percentage = null, int $precision = 2): float
    {
        if ($percentage === null)
        {
            $percentage = $this->dto->getTax();
        }

        return $this->getFloatWithPrecision($netto * ($percentage / 100), $precision);
    }

    /**
     * @inheritdoc
     */
    public function percentage(float $percentageValue, ?float $netto = null, int $precision = 2): float
    {
        return $this->getFloatWithPrecision(($percentageValue / $netto) * 100, $precision);
    }

    /**
     * @inheritdoc
     */
    public function squareMetre(float $length, float $width, int $precision = 2): float
    {
        return $this->getFloatWithPrecision(($length * $width), $precision);
    }

    /**
     * @inheritdoc
     */
    public function cubicMetre(float $length, float $width, float $height, int $precision = 2): float
    {
        return $this->getFloatWithPrecision(($length * $width * $height), $precision);
    }

    /**
     * @inheritdoc
     */
    public function cubicInLitre(float $cubicMetre): float
    {
        return $cubicMetre * 1000;
    }

    /**
     * @inheritdoc
     */
    public function mph(float $miles, int $hours): float
    {
        return $miles / $hours;
    }

    /**
     * @inheritdoc
     */
    public function kmh(float $km, int $hours): float
    {
        return $km / $hours;
    }

    /**
     * @inheritdoc
     */
    public function kmInMiles(float $km, int $precision = 2): float
    {
        return $this->getFloatWithPrecision($km / 1.609344, $precision);
    }

    /**
     * @inheritdoc
     */
    public function milesInKm(float $miles, int $precision = 2): float
    {
        return $this->getFloatWithPrecision($miles * 1.609344, $precision);
    }

    /**
     * @inheritdoc
     */
    public function temperature(float $value, MathEnum $sourceScale, MathEnum $targetScale, int $precision = 2): float
    {
        return match ($targetScale)
        {
            MathEnum::CELSIUS => $this->toCelsius($value, $sourceScale, $precision),
            MathEnum::KELVIN => $this->toKelvin($value, $sourceScale, $precision),
            MathEnum::RANKINE => $this->toRankine($value, $sourceScale, $precision),
            MathEnum::FAHRENHEIT => $this->toFahrenheit($value, $sourceScale, $precision),
            MathEnum::REAUMUR => $this->toReaumur($value, $sourceScale, $precision),
            default => 0.0,
        };
    }

    /**
     * @inheritdoc
     */
    public function toCelsius(float $value, MathEnum $sourceScale, int $precision = 2): float
    {
        $celsius = match ($sourceScale)
        {
            MathEnum::KELVIN => $value - 273.15,
            MathEnum::RANKINE => ($value - 491.67) * (5 / 9),
            MathEnum::FAHRENHEIT => ($value - 32) * (5 / 9),
            MathEnum::REAUMUR => $value * (5 / 4),
            default => $value,
        };

        if ($celsius <= -273.15)
        {
            return -273.15;
        }

        return $this->getFloatWithPrecision($celsius, $precision);
    }

    /**
     * @inheritdoc
     */
    public function toKelvin(float $value, MathEnum $sourceScale, int $precision = 2): float
    {
        $kelvin = match ($sourceScale)
        {
            MathEnum::CELSIUS => $value + 273.15,
            MathEnum::RANKINE => $value * (5 / 9),
            MathEnum::FAHRENHEIT => ($value + 459.67) * (5 / 9),
            MathEnum::REAUMUR => $value * (5 / 4) + 273.15,
            default => $value,
        };

        if ($kelvin <= 0)
        {
            return 0;
        }

        return $this->getFloatWithPrecision($kelvin, $precision);
    }

    /**
     * @inheritdoc
     */
    public function toRankine(float $value, MathEnum $sourceScale, int $precision = 2): float
    {
        $rankine = match ($sourceScale)
        {
            MathEnum::CELSIUS => ($value + 273.15) * (9 / 5),
            MathEnum::KELVIN => $value * (9 / 5),
            MathEnum::FAHRENHEIT => $value + 459.67,
            MathEnum::REAUMUR => $value * (9 / 4) + 491.67,
            default => $value,
        };

        if ($rankine <= 0)
        {
            return 0;
        }

        return $this->getFloatWithPrecision($rankine, $precision);
    }

    /**
     * @inheritdoc
     */
    public function toFahrenheit(float $value, MathEnum $sourceScale, int $precision = 2): float
    {
        $fahrenheit = match ($sourceScale)
        {
            MathEnum::CELSIUS => $value * (9 / 5) + 32,
            MathEnum::KELVIN => $value * (9 / 5) - 459.67,
            MathEnum::RANKINE => $value - 459.67,
            MathEnum::REAUMUR => $value * (9 / 4) + 32,
            default => $value,
        };

        if ($fahrenheit <= -459.67)
        {
            return -459.67;
        }

        return $this->getFloatWithPrecision($fahrenheit, $precision);
    }

    /**
     * @inheritdoc
     */
    public function toReaumur(float $value, MathEnum $sourceScale, int $precision = 2): float
    {
        $reaumur = match ($sourceScale)
        {
            MathEnum::CELSIUS => $value * (4 / 5),
            MathEnum::KELVIN => ($value - 273.15) * (4 / 5),
            MathEnum::RANKINE => ($value - 491.67) * (4 / 9),
            MathEnum::FAHRENHEIT => ($value - 32) * (4 / 9),
            default => $value,
        };

        if ($reaumur <= -218.52)
        {
            return -218.52;
        }

        return $this->getFloatWithPrecision($reaumur, $precision);
    }

    private function getFloatWithPrecision(float $float, int $precision): float
    {
        return (float)number_format($float, $precision);
    }
}
