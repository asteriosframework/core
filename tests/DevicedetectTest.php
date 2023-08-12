<?php

namespace Asterios\Test;

use Asterios\Core\Devicedetect;
use Detection\MobileDetect;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class DevicedetectTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @test
     * @dataProvider is_mobile_provider
     * @param bool $return_value
     * @param bool $expected_value
     */
    public function is_mobile(bool $return_value, bool $expected_value): void
    {
        /** @var MobileDetect|m\MockInterface $mobile_detect_mock */
        $mobile_detect_mock = m::mock(MobileDetect::class);
        $mobile_detect_mock->shouldReceive('isMobile')
            ->andReturn($return_value);

        $result = Devicedetect::forge($mobile_detect_mock)
            ->is_mobile();

        self::assertEquals($expected_value, $result);
    }

    /**
     * @test
     * @dataProvider is_tablet_provider
     * @param bool $return_value
     * @param bool $expected_value
     */
    public function is_tablet(bool $return_value, bool $expected_value): void
    {
        /** @var MobileDetect|m\MockInterface $mobile_detect_mock */
        $mobile_detect_mock = m::mock(MobileDetect::class);
        $mobile_detect_mock->shouldReceive('isTablet')
            ->andReturn($return_value);

        $result = Devicedetect::forge($mobile_detect_mock)
            ->is_tablet();

        self::assertEquals($expected_value, $result);
    }

    /**
     * @test
     * @dataProvider is_desktop_provider
     * @param bool $is_mobile
     * @param bool $is_tablet
     * @param bool $expected_value
     */
    public function is_desktop(bool $is_mobile, bool $is_tablet, bool $expected_value): void
    {
        /** @var MobileDetect|m\MockInterface $mobile_detect_mock */
        $mobile_detect_mock = m::mock(MobileDetect::class);
        $mobile_detect_mock->shouldReceive('isMobile')
            ->andReturn($is_mobile);

        $mobile_detect_mock->shouldReceive('isTablet')
            ->andReturn($is_tablet);

        $result = Devicedetect::forge($mobile_detect_mock)
            ->is_desktop();
        self::assertEquals($expected_value, $result);
    }

    /**
     * @test
     */
    public function get_http_headers(): void
    {
        /** @var MobileDetect|m\MockInterface $mobile_detect_mock */
        $mobile_detect_mock = m::mock(MobileDetect::class);
        $mobile_detect_mock->shouldReceive('getHttpHeaders')
            ->andReturn(['key' => 'value']);

        $result = Devicedetect::forge($mobile_detect_mock)
            ->get_http_headers();

        self::assertIsArray($result);
    }

    /**
     * @test
     * @dataProvider get_http_header_provider
     * @param string $key
     * @param string|null $value
     * @param string|null $expected_value
     */
    public function get_http_header(string $key, ?string $value, ?string $expected_value): void
    {
        /** @var MobileDetect|m\MockInterface $mobile_detect_mock */
        $mobile_detect_mock = m::mock(MobileDetect::class);
        $mobile_detect_mock->shouldReceive('getHttpHeader')
            ->with($key)
            ->andReturn($value);

        $result = Devicedetect::forge($mobile_detect_mock)
            ->get_http_header($key);

        self::assertEquals($expected_value, $result);
    }

    /**
     * @test
     */
    public function get_user_agent(): void
    {
        /** @var MobileDetect|m\MockInterface $mobile_detect_mock */
        $mobile_detect_mock = m::mock(MobileDetect::class);
        $mobile_detect_mock->shouldReceive('getUserAgent')
            ->andReturn('User Agent');

        $result = Devicedetect::forge($mobile_detect_mock)
            ->get_user_agent();

        self::assertEquals('User Agent', $result);
    }

    ########## Provider ##########

    public static function is_mobile_provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    public static function is_tablet_provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    public static function is_desktop_provider(): array
    {
        return [
            [true, true, false],
            [true, false, false],
            [false, true, false],
            [false, false, true],
        ];
    }

    public static function get_http_header_provider(): array
    {
        return [
            ['key', 'value', 'value'],
            ['key', null, null],
        ];
    }
}
