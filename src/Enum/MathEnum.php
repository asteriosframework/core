<?php declare(strict_types=1);

namespace Asterios\Core\Enum;

enum MathEnum
{
    case KMH;
    case MPH;
    case MILES;
    case KM;
    case CELSIUS;
    case KELVIN;
    case RANKINE;
    case FAHRENHEIT;
    case REAUMUR;

    public function unit(): string
    {
        return match ($this)
        {
            self::KMH => 'kmh',
            self::MPH => 'mph',
            self::MILES => 'miles',
            self::KM => 'km',
            self::CELSIUS => 'celsius',
            self::KELVIN => 'kelvin',
            self::RANKINE => 'rankine',
            self::FAHRENHEIT => 'fahrenheit',
            self::REAUMUR => 'reaumur',
        };
    }
}
