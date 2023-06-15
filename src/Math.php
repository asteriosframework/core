<?php declare(strict_types=1);

namespace Asterios\Core;

use Asterios\Core\Interfaces\MathInterface;

class Math implements MathInterface
{
    /**
     * @var  string  Distance in miles
     */
    const DISTANCE_UNIT_MILES = 'miles';

    /**
     * @var  string  Distance in kilometer
     */
    const DISTANCE_UNIT_KM = 'km';

    /**
     * @var  string  Speed in kilometers per hour
     */
    const SPEED_UNIT_KMH = 'kmh';

    /**
     * @var  string Speed in miles per hour
     */
    const SPEED_UNIT_MPH = 'mph';

    /**
     * @var  string  Temperature in celsius
     */
    const TEMPERATURE_CELSIUS = 'celsius';

    /**
     * @var  string  Temperature in kelvin
     */
    const TEMPERATURE_KELVIN = 'kelvin';

    /**
     * @var  string  Temperature in rankine
     */
    const TEMPERATURE_RANKINE = 'rankine';

    /**
     * @var  string  Temperature in fahrenheit
     */
    const TEMPERATURE_FAHRENHEIT = 'fahrenheit';

    /**
     * @var  string  Temperature in reaumur
     */
    const TEMPERATURE_REAUMUR = 'reaumur';

    /**
     * Array with allowed distance units
     * @var   array $allowed_distance_units
     */
    private static $allowed_distance_units = [
        self::DISTANCE_UNIT_MILES,
        self::DISTANCE_UNIT_KM,
    ];

    /**
     * Array with allowed speed units
     * @var   array $allowed_speed_units
     */
    private static $allowed_speed_units = [
        self::SPEED_UNIT_MPH,
        self::SPEED_UNIT_KMH,
    ];

    private static $allowed_temperature_scales = [
        self::TEMPERATURE_CELSIUS,
        self::TEMPERATURE_KELVIN,
        self::TEMPERATURE_RANKINE,
        self::TEMPERATURE_FAHRENHEIT,
        self::TEMPERATURE_REAUMUR,
    ];

    protected float $tax = 19;
    protected string $currency = 'EUR';

    public function setTax(float $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public static function forge(): self
    {
        return new self();
    }

    /**
     * @inheritdoc
     */
    public function netto(float $brutto, int $precision = 2, ?float $tax = null): float
    {
        if (null === $tax)
        {
            $tax = $this->getTax();
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
            $tax = $this->getTax();
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
            $percentage = $this->getTax();
        }

        return $this->getFloatWithPrecision($netto * ($percentage / 100), $precision);
    }

    /**
     * @inheritdoc
     */
    public function percentage(float $percentageValue, float $netto = null, int $precision = 2): float
    {
        return $this->getFloatWithPrecision(($percentageValue / $netto) * 100, $precision);
    }

    /**
     * This function calculate the square metre value based on given values.
     *
     * @param mixed $length
     * @param mixed $width
     * @return mixed
     */
    public static function square_metre($length, $width)
    {
        if (!is_numeric($length) || !is_numeric($width))
        {
            return false;
        }

        return $length * $width;
    }

    /**
     * This function calculate the cubic metre value based on given values.
     *
     * @param mixed $length
     * @param mixed $width
     * @param mixed $height
     * @return mixed
     */
    public static function cubic_metre($length, $width, $height)
    {
        if (!is_numeric($length) || !is_numeric($width) || !is_numeric($height))
        {
            return false;
        }

        return $length * $width * $height;
    }

    /**
     * This function calculate how many litre water is given cubic metre.
     *
     * @param mixed $cubic_metre
     * @return mixed
     */
    public static function cubic_in_litre($cubic_metre)
    {
        return $cubic_metre * 1000;
    }

    /**
     * This function return miles per hour based on given values.
     *
     * @param mixed $miles
     * @param mixed $time
     * @return  mixed
     */
    public static function mph($miles, $time)
    {
        return $miles / $time;
    }

    /**
     * This function return kilometers per hour based on given values.
     *
     * @param mixed $km
     * @param mixed $time
     * @return  mixed
     */
    public static function kmh($km, $time)
    {
        return $km / $time;
    }

    /**
     * This function return kmh or mph based on given miles or kilometers.
     *
     * @param mixed $distance
     * @param mixed $time
     * @param mixed $distance_unit
     * @param mixed $speed_unit
     * @return  mixed
     */
    public static function speed($distance, $time, $distance_unit = self::DISTANCE_UNIT_KM, $speed_unit = self::SPEED_UNIT_KMH)
    {

        if (!static::is_distance_unit($distance_unit) || !static::is_speed_unit($speed_unit))
        {
            $return = false;
        }

        if ($distance_unit === self::DISTANCE_UNIT_KM && $speed_unit == self::SPEED_UNIT_KMH)
        {
            $return = static::kmh($distance, $time);
        }
        elseif ($distance_unit === self::DISTANCE_UNIT_KM && $speed_unit == self::SPEED_UNIT_MPH)
        {
            $return = round(static::mph(static::km_in_miles($distance), $time), 1);
        }
        elseif ($distance_unit === self::DISTANCE_UNIT_MILES && $speed_unit == self::SPEED_UNIT_KMH)
        {
            $return = round(static::kmh(static::miles_in_km($distance), $time), 1);
        }
        elseif ($distance_unit === self::DISTANCE_UNIT_MILES && $speed_unit == self::SPEED_UNIT_MPH)
        {
            $return = round(static::mph($distance, $time), 1);
        }
        else
        {
            $return = false;
        }

        return $return;
    }

    /**
     * This function return given kilometers in miles.
     *
     * @param mixed $km
     * @return  mixed
     */
    public static function km_in_miles($km)
    {
        return $km / 1.609344;
    }

    /**
     * This function return given miles in kilometer.
     *
     * @param mixed $miles
     * @return  mixed
     */
    public static function miles_in_km($miles)
    {
        return $miles * 1.609344;
    }

    /**
     * This private function check if given distance unit is a valid distance unit.
     *
     * @param mixed $distance_unit
     * @return  boolean
     */
    private static function is_distance_unit($distance_unit)
    {
        if (!in_array($distance_unit, static::$allowed_distance_units))
        {
            return false;
        }

        return true;
    }

    /**
     * This private function check if given speed unit is a valid speed unit.
     *
     * @param mixed $speed_unit
     * @return  boolean
     */
    private static function is_speed_unit($speed_unit)
    {
        if (!in_array($speed_unit, static::$allowed_speed_units))
        {
            return false;
        }

        return true;
    }

    /**
     * This function returns given value from given temperature scale into given source temperature scale.
     *
     * @param numeric $value
     * @param string $source_scale
     * @param string $target_scale
     * @return mixed|bolean
     */
    public static function temperature($value, $source_scale, $target_scale)
    {
        if (!in_array($source_scale, static::$allowed_temperature_scales) || !in_array($target_scale, static::$allowed_temperature_scales))
        {
            return false;
        }

        switch ($target_scale)
        {
            case self::TEMPERATURE_CELSIUS:
                return static::to_celsius($value, $source_scale);
            case self::TEMPERATURE_KELVIN:
                return static::to_kelvin($value, $source_scale);
            case self::TEMPERATURE_RANKINE:
                return static::to_rankine($value, $source_scale);
            case self::TEMPERATURE_FAHRENHEIT:
                return static::to_fahrenheit($value, $source_scale);
            case self::TEMPERATURE_REAUMUR:
                return static::to_reaumur($value, $source_scale);
        }
    }

    /**
     * @param $value
     * @param $source_scale
     * @return mixed
     */
    private static function to_celsius($value, $source_scale)
    {
        if (!in_array($source_scale, static::$allowed_temperature_scales))
        {
            return false;
        }

        switch ($source_scale)
        {
            case self::TEMPERATURE_KELVIN:
                $celsius_value = $value - 273.15;
                break;
            case self::TEMPERATURE_RANKINE:
                $celsius_value = ($value - 491.67) * (5 / 9);
                break;
            case self::TEMPERATURE_FAHRENHEIT:
                $celsius_value = ($value - 32) * (5 / 9);
                break;
            case self::TEMPERATURE_REAUMUR:
                $celsius_value = $value * (5 / 4);
                break;
            default:
                $celsius_value = $value;
        }

        // check if absolute zero is given
        if ($celsius_value <= -273.15)
        {
            return -273.15;
        }
        else
        {
            return $celsius_value;
        }
    }

    /**
     * @param $value
     * @param $source_scale
     * @return mixed
     */
    private static function to_kelvin($value, $source_scale)
    {
        if (!in_array($source_scale, static::$allowed_temperature_scales))
        {
            return false;
        }

        switch ($source_scale)
        {
            case self::TEMPERATURE_CELSIUS:
                $kelvin_value = $value + 273.15;
                break;
            case self::TEMPERATURE_RANKINE:
                $kelvin_value = $value * (5 / 9);
                break;
            case self::TEMPERATURE_FAHRENHEIT:
                $kelvin_value = ($value + 459.67) * (5 / 9);
                break;
            case self::TEMPERATURE_REAUMUR:
                $kelvin_value = $value * (5 / 4) + 273.15;
                break;
            default:
                $kelvin_value = $value;
        }

        // check if absolute zero is given
        if ($kelvin_value <= 0)
        {
            return 0;
        }
        else
        {
            return $kelvin_value;
        }
    }

    /**
     * @param $value
     * @param $source_scale
     * @return mixed
     */
    private static function to_rankine($value, $source_scale)
    {
        if (!in_array($source_scale, static::$allowed_temperature_scales))
        {
            return false;
        }

        switch ($source_scale)
        {
            case self::TEMPERATURE_CELSIUS:
                $rankine_value = ($value + 273.15) * (9 / 5);
                break;
            case self::TEMPERATURE_KELVIN:
                $rankine_value = $value * (9 / 5);
                break;
            case self::TEMPERATURE_FAHRENHEIT:
                $rankine_value = $value + 459.67;
                break;
            case self::TEMPERATURE_REAUMUR:
                $rankine_value = $value * (9 / 4) + 491.67;
                break;
            default:
                $rankine_value = $value;
        }

        // check if absolute zero is given
        if ($rankine_value <= 0)
        {
            return 0;
        }
        else
        {
            return $rankine_value;
        }
    }

    /**
     * @param $value
     * @param $source_scale
     * @return mixed
     */
    private static function to_fahrenheit($value, $source_scale)
    {
        if (!in_array($source_scale, static::$allowed_temperature_scales))
        {
            return false;
        }

        switch ($source_scale)
        {
            case self::TEMPERATURE_CELSIUS:
                $fahrenheit_value = $value * (9 / 5) + 32;
                break;
            case self::TEMPERATURE_KELVIN:
                $fahrenheit_value = $value * (9 / 5) - 459.67;
                break;
            case self::TEMPERATURE_RANKINE:
                $fahrenheit_value = $value - 459.67;
                break;
            case self::TEMPERATURE_REAUMUR:
                $fahrenheit_value = $value * (9 / 4) + 32;
                break;
            default:
                $fahrenheit_value = $value;
        }

        // check if absolute zero is given
        if ($fahrenheit_value <= -459.67)
        {
            return -459.67;
        }
        else
        {
            return $fahrenheit_value;
        }
    }

    /**
     * @param $value
     * @param $source_scale
     * @return mixed
     */
    private static function to_reaumur($value, $source_scale)
    {
        if (!in_array($source_scale, static::$allowed_temperature_scales))
        {
            return false;
        }

        switch ($source_scale)
        {
            case self::TEMPERATURE_CELSIUS:
                $reaumur_value = $value * (4 / 5);
                break;
            case self::TEMPERATURE_KELVIN:
                $reaumur_value = ($value - 273.15) * (4 / 5);
                break;
            case self::TEMPERATURE_RANKINE:
                $reaumur_value = ($value - 491.67) * (4 / 9);
                break;
            case self::TEMPERATURE_FAHRENHEIT:
                $reaumur_value = ($value - 32) * (4 / 9);
                break;
            default:
                $reaumur_value = $value;
        }

        // check if absolute zero is given
        if ($reaumur_value <= -218.52)
        {
            return -218.52;
        }
        else
        {
            return $reaumur_value;
        }
    }

    private function getFloatWithPrecision(float $float, int $precision): float
    {
        return (float)number_format($float, $precision);
    }
}