<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Dto\MathDto;
use Asterios\Core\Enum\MathEnum;
use Asterios\Core\Math;
use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
{
    protected Math $testedClass;
    protected MathDto $dto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dto = new MathDto();
        $this->dto->setTax(19);
        $this->dto->setCurrency('EUR');

        $this->testedClass = Math::forge($this->dto);
    }

    public function test_netto(): void
    {
        $actual = $this->testedClass->netto(19.95);

        self::assertEquals(16.76, $actual);
    }

    public function test_brutto(): void
    {
        $actual = $this->testedClass->brutto(16.764);

        self::assertEquals(19.95, $actual);
    }

    /**
     * @dataProvider percentageValueProvider
     */
    public function test_percentageValue(float $netto, ?float $percentage, float $expected): void
    {
        $actual = $this->testedClass->percentageValue($netto, $percentage);

        self::assertEquals($expected, $actual);
    }

    public function test_percentage(): void
    {
        $actual = $this->testedClass->percentage(3.19, 16.764, 0);

        self::assertEquals(19, $actual);
    }

    /**
     * @dataProvider squareMetreProvider
     */
    public function test_squareMetre(float $length, float $width, int $precision, float $expected): void
    {
        $actual = $this->testedClass->squareMetre($length, $width, $precision);

        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider cubicMetreProvider
     */
    public function test_cubicMetre(float $length, float $width, float $height, int $precision, float $expected): void
    {
        $actual = $this->testedClass->cubicMetre($length, $width, $height, $precision);

        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider cubicInLitreProvider
     */
    public function test_cubicInLitre(float $cubicMetre, float $expected): void
    {

        $actual = $this->testedClass->cubicInLitre($cubicMetre);

        self::assertEquals($expected, $actual);

    }

    public function test_mph(): void
    {
        $actual = $this->testedClass->mph(50, 1);

        self::assertEquals(50, $actual);
    }

    public function test_kmh(): void
    {
        $actual = $this->testedClass->kmh(70, 1);

        self::assertEquals(70, $actual);
    }

    public function test_kmInMiles(): void
    {
        $actual = $this->testedClass->kmInMiles(100, 0);

        self::assertEquals(62, $actual);
    }

    public function test_milesInKm(): void
    {
        $actual = $this->testedClass->milesInKm(100, 0);

        self::assertEquals(161, $actual);
    }

    /**
     * @dataProvider temperatureProvider
     */
    public function test_temperature(float $value, MathEnum $source, MathEnum $target, float $expected): void
    {
        $actual = $this->testedClass->temperature($value, $source, $target);

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
            [16.764, null, 3.19],
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
            [23, MathEnum::CELSIUS, MathEnum::KELVIN, 296.15],
            [23, MathEnum::CELSIUS, MathEnum::RANKINE, 533.07],
            [23, MathEnum::CELSIUS, MathEnum::FAHRENHEIT, 73.4],
            [23, MathEnum::CELSIUS, MathEnum::REAUMUR, 18.4],
            [99.5, MathEnum::FAHRENHEIT, MathEnum::CELSIUS, 37.5],
            [-800, MathEnum::FAHRENHEIT, MathEnum::CELSIUS, -273.15],
            [-300, MathEnum::CELSIUS, MathEnum::KELVIN, 0],
            [-300, MathEnum::CELSIUS, MathEnum::RANKINE, 0],
            [-300, MathEnum::CELSIUS, MathEnum::FAHRENHEIT, -459.67],
            [-300, MathEnum::CELSIUS, MathEnum::REAUMUR, -218.52],
        ];
    }
}
