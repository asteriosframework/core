<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Enum\Math\TemperatureScale;
use Asterios\Core\Math\Math;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
{
    protected Math $testedClass;

    protected function setUp(): void
    {
        parent::setUp();


        $this->testedClass = new Math();
    }

    public function test_net(): void
    {
        $actual = $this->testedClass->tax()->net(19.95, 19);

        self::assertEquals(16.76, $actual);
    }
    public function test_gross(): void
    {
        $actual = $this->testedClass->tax()->gross(16.764, 19);

        self::assertEquals(19.95, $actual);
    }


    #[DataProvider('percentageValueProvider')]
    public function test_percentage_of(float $netto, ?float $percentage, float $expected): void
    {
        $actual = $this->testedClass->tax()->percentageOf($netto, $percentage);

        self::assertEquals($expected, $actual);
    }

    public function test_percentage(): void
    {
        $actual = $this->testedClass->tax()->percentage(3.19, 16.764, 0);

        self::assertEquals(19, $actual);
    }

    #[DataProvider('squareMetreProvider')]
    public function test_squareMetre(float $length, float $width, int $precision, float $expected): void
    {
        $actual = $this->testedClass->geometry()->squareMetres($length, $width, $precision);

        self::assertEquals($expected, $actual);
    }

    #[DataProvider('cubicMetreProvider')]
    public function test_cubicMetre(float $length, float $width, float $height, int $precision, float $expected): void
    {
        $actual = $this->testedClass->geometry()->cubicMetres($length, $width, $height, $precision);

        self::assertEquals($expected, $actual);
    }

    #[DataProvider('cubicInLitreProvider')]
    public function test_cubicInLitre(float $cubicMetre, float $expected): void
    {

        $actual = $this->testedClass->geometry()->cubicMetresToLitres($cubicMetre);

        self::assertEquals($expected, $actual);

    }

    public function test_kmToMiles(): void
    {
        $actual = $this->testedClass->units()->kmToMiles(100, 0);

        self::assertEquals(62, $actual);
    }

    public function test_milesToKm(): void
    {
        $actual = $this->testedClass->units()->milesToKm(100, 0);

        self::assertEquals(161, $actual);
    }

    #[DataProvider('temperatureProvider')]
    public function test_temperature(float $value, TemperatureScale $source, TemperatureScale $target, float $expected): void
    {
        $actual = $this->testedClass->temperature()->convert($value, $source, $target);

        self::assertEquals($expected, $actual);
    }

    // Data provider

    /**
     * @return array[]
     */
    public static function percentageValueProvider(): array
    {
        return [
            [16.764, 7, 1.17],
            [16.764, 19, 3.19],
        ];
    }

    /**
     * @return array[]
     */
    public static function squareMetreProvider(): array
    {
        return [
            [4, 3.5, 2, 14],
            [4.5, 3.8, 3, 17.1],
        ];
    }

    /**
     * @return array[]
     */
    public static function cubicMetreProvider(): array
    {
        return [
            [3, 3, 3, 2, 27],
            [0.30, 0.30, 0.70, 3, 0.063],
        ];
    }

    /**
     * @return array[]
     */
    public static function cubicInLitreProvider(): array
    {
        return [
            [0.001, 1],
            [1, 1000],
        ];
    }

    public static function temperatureProvider(): array
    {
        return [
            [23, TemperatureScale::CELSIUS, TemperatureScale::KELVIN, 296.15],
            [23, TemperatureScale::CELSIUS, TemperatureScale::RANKINE, 533.07],
            [23, TemperatureScale::CELSIUS, TemperatureScale::FAHRENHEIT, 73.4],
            [23, TemperatureScale::CELSIUS, TemperatureScale::REAUMUR, 18.4],
            [99.5, TemperatureScale::FAHRENHEIT, TemperatureScale::CELSIUS, 37.5],
            [-800, TemperatureScale::FAHRENHEIT, TemperatureScale::CELSIUS, -273.15],
            [-300, TemperatureScale::CELSIUS, TemperatureScale::KELVIN, 0],
            [-300, TemperatureScale::CELSIUS, TemperatureScale::RANKINE, 0],
            [-300, TemperatureScale::CELSIUS, TemperatureScale::FAHRENHEIT, -459.67],
            [-300, TemperatureScale::CELSIUS, TemperatureScale::REAUMUR, -218.52],
        ];
    }
}
