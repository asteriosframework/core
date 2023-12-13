<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Asterios;
use Asterios\Core\Config;
use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\ViewTemplateAccessException;
use Asterios\Core\View;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @runTestsInSeparateProcesses
 */
class ViewTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     * @runInSeparateProcess
     * @throws ConfigLoadException
     */
    public function forge_exception(): void
    {
        $testdata_path = __DIR__ . '/..' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR;
        Asterios::setEnvironment(Asterios::DEVELOPMENT);
        Config::set_config_path($testdata_path . 'viewconfig');

        Config::set_memory('TPLPATH', $testdata_path . 'views');

        $this->expectException(ViewTemplateAccessException::class);
        $this->expectExceptionMessage('FATAL ERROR: Could not load error-template "404"!');

        ob_start();
        View::forge('foo');
        ob_get_clean();
    }

    /**
     * @test
     * @runInSeparateProcess
     * @throws ConfigLoadException
     */
    public function forge(): void
    {
        $testdata_path = __DIR__ . '/..' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR;
        Asterios::setEnvironment(Asterios::DEVELOPMENT);
        Config::set_config_path($testdata_path . 'viewconfig');

        Config::set_memory('TPLPATH', $testdata_path . 'views' . DIRECTORY_SEPARATOR);

        \ob_start();
        View::forge('index');
        $result = \ob_get_clean();
        self::assertEquals('<h1>Hello World</h1>', $result);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @throws ConfigLoadException
     */
    public function forge_404(): void
    {
        $testdata_path = __DIR__ . '/..' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR;
        Asterios::setEnvironment(Asterios::DEVELOPMENT);
        Config::set_config_path($testdata_path . 'viewconfig');

        Config::set_memory('TPLPATH', $testdata_path . 'views' . DIRECTORY_SEPARATOR);

        \ob_start();
        View::forge('welcome');
        $result = \ob_get_clean();

        self::assertEquals('<h1>Error 404</h1>', $result);
    }
}