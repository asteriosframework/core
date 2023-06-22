<?php
declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Asterios;
use Asterios\Core\Config;
use Asterios\Core\Router;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class RouterTest extends TestCase
{
    /**
     * @test
     */
    public function prepare_routes(): void
    {
        $expected_value = [
            '/v1/foo/(\w+)' => [
                ['GET', 'ControllerFoo/current_one'],
            ],
            '/v1/foo/bar/(\w+)' => [
                ['GET', 'ControllerFoo/bar'],
            ],
            '/v2/foo/bar/(\d+)' => [
                ['GET', 'ControllerFoo/bar_one'],
            ],
        ];

        Asterios::set_environment(Asterios::DEVELOPMENT);
        Config::set_config_path(getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR . 'config');

        $router = new Router('routes_router');
        $result = $router->get_routes();

        self::assertEquals($expected_value, $result);
    }
}
