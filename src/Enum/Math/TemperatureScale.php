<?php declare(strict_types=1);

namespace Asterios\Core\Enum\Math;

enum TemperatureScale: string
{
    case CELSIUS = 'celsius';
    case KELVIN = 'kelvin';
    case RANKINE = 'rankine';
    case FAHRENHEIT = 'fahrenheit';
    case REAUMUR = 'reaumur';
}
