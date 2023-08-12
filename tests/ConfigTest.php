<?php
declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Asterios;
use Asterios\Core\Config;
use Asterios\Core\Exception\ConfigLoadException;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class ConfigTest extends TestCase
{
    /**
     * @var string
     */
    protected $config_path;

    protected function setUp(): void
    {
        $this->config_path = implode(DIRECTORY_SEPARATOR, [getcwd(), 'tests', 'testdata', 'config']);
    }

    /**
     * @test
     */
    public function load_default_file_exception(): void
    {
        $default_file_name = 'foo';

        Asterios::set_environment(Asterios::DEVELOPMENT);
        Config::set_config_path($this->config_path);

        $this->expectException(ConfigLoadException::class);
        $this->expectExceptionMessage('Could not load default config file ' . Config::get_config_path() . DIRECTORY_SEPARATOR . $default_file_name . '.php');

        Config::load($default_file_name);
    }

    /**
     * @dataProvider load_provider
     * @test
     * @param string $config_file
     * @param string $environment
     * @param array $expected_value
     * @throws ConfigLoadException
     */
    public function load(string $config_file, string $environment, array $expected_value): void
    {
        Asterios::set_environment($environment);
        Config::set_config_path($this->config_path);

        $config = Config::load($config_file);

        self::assertEquals($expected_value, $config);
    }

    /**
     * @test
     */
    public function get_is_object(): void
    {
        $default_file_name = 'default';

        Asterios::set_environment(Asterios::DEVELOPMENT);
        Config::set_config_path($this->config_path);

        $config = Config::get($default_file_name);

        self::assertIsObject($config);
    }

    /**
     * @test
     */
    public function get_exception(): void
    {
        $default_file_name = 'foo';

        Asterios::set_environment(Asterios::DEVELOPMENT);
        Config::set_config_path($this->config_path);

        $this->expectException(ConfigLoadException::class);

        Config::get($default_file_name);
    }

    /**
     * @test
     * @dataProvider get_dot_notation_provider
     * @param string $config_file
     * @param string $item
     * @param mixed $default_value
     * @param mixed $expected_value
     * @throws ConfigLoadException
     */
    public function get_item_dot_notation(string $config_file, string $item, $default_value, $expected_value): void
    {
        Asterios::set_environment(Asterios::DEVELOPMENT);
        Config::set_config_path($this->config_path);

        $config = Config::get($config_file, $item, $default_value);

        self::assertEquals($expected_value, $config);
    }

    /**
     * @test
     * @dataProvider get_memory_provider
     * @param string $item
     * @param $value
     * @param $expected_value
     * @param mixed|null $default
     */
    public function get_memory(string $item, $value, $expected_value, $default = null): void
    {
        Config::set_memory($item, $value);
        $config = Config::get_memory($item, $default);

        self::assertEquals($expected_value, $config);
    }

    /**
     * @test
     * @dataProvider get_memory_default_provider
     * @param string $item
     * @param $expected_value
     * @param mixed $default
     */
    public function get_memory_default(string $item, $expected_value, $default = null): void
    {
        $config = Config::get_memory($item, $default);

        self::assertEquals($expected_value, $config);
    }

    ########## Provider ##########

    public static function load_provider(): array
    {
        return [
            [
                'default',
                Asterios::DEVELOPMENT,
                [
                    'debug' => true,
                    'default' => [
                        'timezone' => 'Europe/Berlin',
                    ],
                ],
            ],
            [
                'routes',
                Asterios::DEVELOPMENT,
                [
                    '/v1/form/current/(\w+)' => [
                        ['GET', 'ControllerForm/current_one'],
                    ],
                ],
            ],
            [
                'default',
                Asterios::PRODUCTION,
                [
                    'debug' => false,
                    'default' => [
                        'timezone' => 'Europe/Berlin',
                    ],
                    'foo' => 'bar',
                ],
            ],
        ];
    }

    public static function get_dot_notation_provider(): array
    {
        return [
            ['dotnotation', 'logger.path', null, 'logs'],
            ['dotnotation', 'logger.level', 'debug', 'debug'],
            [
                'dotnotation',
                'logger',
                null,
                (object)[
                    'path' => 'logs',
                    'filename' => 'asterios',
                ],
            ],
            ['dotnotation', 'mail.templates.login.de.content.data.content', null, 'Hello World'],
        ];
    }

    public static function get_memory_provider(): array
    {
        return [
            ['foo', 'bar', 'bar'],
            ['foo', 1, 1],
            ['foo.foot', true, true],
            ['foo.bar', null, null],
            ['foo.baar', ['key' => 'value'], ['key' => 'value']],
            ['foo.bar', (object)['key' => 'value'], (object)['key' => 'value']],
        ];
    }

    public static function get_memory_default_provider(): array
    {
        return [
            ['bar.foo', null],
            ['foo.bar.baz', false, false],
        ];
    }

}
