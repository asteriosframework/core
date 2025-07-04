<?php
declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\RouterException;
use Asterios\Core\Router;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RouterTest extends TestCase
{
    public function testConstructorLoadsRoutesFromConfig(): void
    {
        $fakeRoutes = [
            'v1' => [
                'middleware' => ['auth'],
                'test' => [
                    ['GET', 'TestController@index'],
                    ['POST', 'TestController@store', ['middleware' => ['log']]],
                ],
            ],
        ];

        $mock = m::mock('alias:Asterios\Core\Config');

        $mock->shouldReceive('get')
            ->once()
            ->with('routes')
            ->andReturn($fakeRoutes);

        $mock->shouldReceive('get_config_path')
            ->andReturn('/fake/path');

        $router = new Router('routes');

        $reflection = new ReflectionClass($router);
        $property = $reflection->getProperty('afterRoutes');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $property->setAccessible(true);
        $afterRoutes = $property->getValue($router);

        $this->assertArrayHasKey('GET', $afterRoutes);
        $this->assertArrayHasKey('POST', $afterRoutes);
        $this->assertCount(1, $afterRoutes['GET']);
        $this->assertEquals('/v1/test', $afterRoutes['GET'][0]['pattern']);
        $this->assertEquals('TestController@index', $afterRoutes['GET'][0]['fn']);
        $this->assertEquals(['auth'], $afterRoutes['GET'][0]['middlewares']);

        $this->assertEquals(['auth', 'log'], $afterRoutes['POST'][0]['middlewares']);
    }

    public function testConstructorThrowsRouterExceptionWhenConfigFails(): void
    {
        $mock = m::mock('alias:Asterios\Core\Config');
        $mock->shouldReceive('get')
            ->once()
            ->with('routes')
            ->andThrow(new ConfigLoadException());

        $mock->shouldReceive('get_config_path')
            ->andReturn('/fake/path');

        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Config file not found!');

        new Router('routes');
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
}
