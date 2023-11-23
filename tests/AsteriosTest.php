<?php
declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Asterios;
use Asterios\Core\Config;
use Asterios\Core\Date;
use Asterios\Core\Exception\AsteriosException;
use Asterios\Core\Exception\ConfigLoadException;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class AsteriosTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Date::set_timezone('Europe/Berlin');
        Asterios::setEnvironment(Asterios::PRODUCTION);
    }

    /**
     * @test
     */
    public function config_exception(): void
    {
        Asterios::setEnvironment(Asterios::DEVELOPMENT);
        Config::set_config_path(getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR . 'config');

        $this->expectException(ConfigLoadException::class);

        Asterios::config('foo', 'bar');
    }

    /**
     * @test
     * @dataProvider config_provider
     * @param string $environment
     * @param string $item
     * @param mixed $expected_value
     * @throws ConfigLoadException
     */
    public function config(string $environment, string $item, $expected_value): void
    {
        Asterios::setEnvironment($environment);
        Config::set_config_path(getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR . 'config');

        $config = Asterios::config($item);

        self::assertEquals($expected_value, $config);
    }

    /**
     * @test
     * @dataProvider set_timezone_provider
     * @param string $timezone
     * @param string $expected_value
     */
    public function set_timezone(string $timezone, string $expected_value): void
    {
        Asterios::setTimezone($timezone);
        $result = Asterios::getTimezone();

        self::assertEquals($expected_value, $result);
    }

    /**
     * @test
     * @throws AsteriosException
     * @throws ConfigLoadException
     */
    public function init_exception(): void
    {
        $config_path = implode(DIRECTORY_SEPARATOR, [getcwd(), 'tests', 'testdata', 'asterios']);
        Asterios::setEnvironment(Asterios::DEVELOPMENT);
        Config::set_config_path($config_path);

        $this->expectException(AsteriosException::class);

        Asterios::init();
        $result = Asterios::isInitialized();
        self::assertTrue($result);
        Asterios::init();
    }

    /**
     * @test
     */
    public function get_encoding(): void
    {
        Asterios::setEncoding('UTF-8');
        $result = Asterios::getEncoding();

        self::assertEquals('UTF-8', $result);
    }

    /**
     * @test
     * @dataProvider is_environment_provider
     */
    public function is_environment(string $method, string $env, bool $expected_value): void
    {
        Asterios::setEnvironment($env);

        $result = Asterios::$method();

        self::assertEquals($expected_value, $result);
    }

    ########## Provider ##########

    public static function config_provider(): array
    {
        return [
            [Asterios::DEVELOPMENT, 'debug', true],
            [Asterios::PRODUCTION, 'debug', false],
            [Asterios::DEVELOPMENT, 'default.timezone', 'Europe/Berlin'],
        ];
    }

    public static function set_timezone_provider(): array
    {
        return [
            ['Europe/Berlin', 'Europe/Berlin'],
            ['Asia/Shanghai', 'Asia/Shanghai'],
        ];
    }

    public static function is_environment_provider(): array
    {
        return [
            ['is_production', Asterios::PRODUCTION, true],
            ['is_production', Asterios::DEVELOPMENT, false],
            ['is_staging', Asterios::STAGING, true],
            ['is_staging', Asterios::PRODUCTION, false],
            ['is_feature', Asterios::FEATURE, true],
            ['is_feature', Asterios::DEVELOPMENT, false],
            ['is_development', Asterios::DEVELOPMENT, true],
            ['is_development', Asterios::FEATURE, false],
        ];
    }
}
