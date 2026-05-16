<?php declare(strict_types=1);

namespace Asterios\Core\Enum;

use Asterios\Core\Enum\Math\TemperatureScale;

/**
 * @deprecated Use TemperatureScale instead.
 */
enum MathEnum: string
{
    case CELSIUS = 'celsius';
    case KELVIN = 'kelvin';
    case RANKINE = 'rankine';
    case FAHRENHEIT = 'fahrenheit';
    case REAUMUR = 'reaumur';

    public function toTemperatureScale(): TemperatureScale
    {
        return TemperatureScale::from($this->value);
    }
}