<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Date;
use Asterios\Core\Exception\DateException;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class DateTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        (new Date)->setTimezone(Date::DEFAULT_TIMEZONE);
    }

    /**
     * @test
     * @dataProvider setTimezoneProvider
     * @param string $timezone
     * @param string $expected
     */
    public function set_timezone(string $timezone, string $expected): void
    {
        (new Date)->setTimezone($timezone);

        $actual = (new Date)->getTimezone();

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider daysInMonthProvider
     * @param int $month
     * @param int $year
     * @param int $expected
     * @throws Exception
     */
    public function days_in_month(int $month, int $year, int $expected): void
    {
        $actual = (new Date)->daysInMonth($month, $year);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider setTimezoneProvider
     * @param string $timezone
     * @param string $expected
     */
    public function setTimezone(string $timezone, string $expected): void
    {
        (new Date)->setTimezone($timezone);

        $actual = (new Date)->getTimezone();

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider daysInMonthProvider
     * @param int $month
     * @param int $year
     * @param int $expected
     * @throws DateException
     */
    public function daysInMonth(int $month, int $year, int $expected): void
    {
        $actual = (new Date)->daysInMonth($month, $year);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function daysInMonthException(): void
    {
        $this->expectException(DateException::class);

        (new Date())->daysInMonth(13, 2012);
    }

    /**
     * @test
     * @dataProvider getTimestampProvider
     * @throws DateException
     */
    public function getTimestamp(string $date, $expected): void
    {
        $actual = (new Date)->getTimestamp($date);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function getTimestampException(): void
    {
        $this->expectException(DateException::class);

        (new Date)->getTimestamp('32.12.2022');
    }

    /**
     * @test
     * @dataProvider formatProvider
     * @throws DateException
     */
    public function format(string $date, string $format, string $expected): void
    {
        $actual = (new Date)->format($date, $format);

        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @throws DateException
     */
    public function formatException(): void
    {
        $this->expectException(DateException::class);

        (new Date)->format('2022-12-32', 'd.m.Y');
    }

    ########## Provider ##########

    public function setTimezoneProvider(): array
    {
        return [
            ['Europe/Berlin', 'Europe/Berlin'],
            ['Asia/Shanghai', 'Asia/Shanghai'],
        ];
    }

    public function daysInMonthProvider(): array
    {
        return [
            [2, 2021, 28],
            [2, 2020, 29],
            [3, 2021, 31],
            [4, 2021, 30],
        ];
    }

    public function getTimestampProvider(): array
    {
        return [
            ['01.01.2022', '1640991600'],
            ['2022-02-19', '1645225200'],
        ];
    }

    public function formatProvider(): array
    {
        return [
            ['2022-01-01', 'd.m.Y', '01.01.2022'],
            ['01.01.2022', 'Y-m-d', '2022-01-01'],
            ['02/16/2022', 'd.m.Y', '16.02.2022'],
        ];
    }
}
