<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\InputService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class InputServiceTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @test
     * @dataProvider getProvider
     */
    public function get($param, $value, $default, $expected_result): void
    {
        if (null !== $value)
        {
            $_GET[$param] = $value;
        }

        $result = (new InputService)->get($param, $default);

        self::assertEquals($expected_result, $result);

        unset($_GET[$param]);
    }

    /**
     * @test
     */
    public function ip(): void
    {
        $result1 = (new InputService)->ip();
        self::assertEquals('', $result1);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $result2 = (new InputService)->ip();
        self::assertEquals('127.0.0.1', $result2);
    }

    /**
     * @test
     * @dataProvider realIpProvider
     */
    public function realIp(string $key, string $value, string $expected_value): void
    {
        $_SERVER[$key] = $value;

        $result = (new InputService)->realIp();

        self::assertEquals($expected_value, $result);
    }

    /**
     * @test
     */
    public function isAjax(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

        $result = (new InputService)->isAjax();

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function referrer(): void
    {
        $result1 = (new InputService)->referrer('default referrer');
        self::assertEquals('default referrer', $result1);

        $_SERVER['HTTP_REFERER'] = 'original HTTP_REFERER';

        $result2 = (new InputService)->referrer('default referrer');
        self::assertEquals('original HTTP_REFERER', $result2);
    }

    /**
     * @test
     */
    public function userAgent(): void
    {
        $result1 = (new InputService)->userAgent('default useragent');
        self::assertEquals('default useragent', $result1);

        $_SERVER['HTTP_USER_AGENT'] = 'original HTTP_USER_AGENT';

        $result2 = (new InputService)->userAgent('default useragent');
        self::assertEquals('original HTTP_USER_AGENT', $result2);
    }

    /**
     * @test
     */
    public function queryString(): void
    {
        $result1 = (new InputService)->queryString('default querystring');
        self::assertEquals('default querystring', $result1);

        $_SERVER['QUERY_STRING'] = 'original QUERY_STRING';

        $result2 = (new InputService)->queryString('default querystring');
        self::assertEquals('original QUERY_STRING', $result2);
    }

    /**
     * @test
     */
    public function phpSelf(): void
    {
        $result1 = (new InputService)->phpSelf();
        self::assertGreaterThan(1, strlen($result1));

        $_SERVER['PHP_SELF'] = 'original PHP_SELF';

        $result2 = (new InputService)->phpSelf();
        self::assertEquals('original PHP_SELF', $result2);
    }

    /**
     * @test
     * @dataProvider cookieProvider
     */
    public function cookie($data, $default, $expected_value): void
    {
        if (null !== $data)
        {
            $_COOKIE['cookie'] = $data;
        }

        $result = (new InputService)->cookie('cookie', $default);
        self::assertEquals($expected_value, $result);

        unset($_COOKIE);
    }

    /**
     * @test
     * @dataProvider postProvider
     */
    public function post($data, $default, $expected_value): void
    {
        if (null !== $data)
        {
            $_POST['data'] = $data;
        }

        $result = (new InputService)->post('data', $default);
        self::assertEquals($expected_value, $result);

        unset($_POST);
    }

    ########## Provider ##########

    public function getProvider(): array
    {
        return [
            ['page', null, null, null],
            ['page', '', null, ''],
            ['page', ' test ', null, 'test'],
            ['page', '10', null, '10'],
            ['filters', ['foo' => 'bar'], null, ['foo' => 'bar']],
            ['test', null, 'foo', 'foo'],
        ];
    }

    public function realIpProvider(): array
    {
        return [
            ['HTTP_CLIENT_IP', 'value HTTP_CLIENT_IP', 'value HTTP_CLIENT_IP'],
            ['HTTP_X_FORWARDED_FOR', 'value HTTP_X_FORWARDED_FOR', 'value HTTP_X_FORWARDED_FOR'],
            ['REMOTE_ADDR', 'value REMOTE_ADDR', 'value REMOTE_ADDR'],
        ];
    }

    public function postProvider(): array
    {
        return [
            [null, null, null],
            ['test', null, 'test'],
            [null, 'test', 'test'],
        ];
    }

    public function cookieProvider(): array
    {
        return [
            [null, null, null],
            ['test', null, 'test'],
            [null, 'test', 'test'],
        ];
    }

}