<?php

declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Support\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    protected function setUp(): void
    {
        Route::$routes = [];
        Route::$groupStack = [];
    }

    public function testGetAddsRoute(): void
    {
        Route::get('demo', 'DemoController@index');
        $route = Route::$routes[0];

        $this->assertSame('GET', $route['method']);
        $this->assertSame('/demo', $route['route']);
        $this->assertSame('DemoController@index', $route['action']);
    }

    public function testPostAddsRoute(): void
    {
        Route::post('submit', 'SubmitController@store');
        $route = Route::$routes[0];

        $this->assertSame('POST', $route['method']);
        $this->assertSame('/submit', $route['route']);
    }

    public function testPutAddsRoute(): void
    {
        Route::put('update', 'UpdateController@update');
        $route = Route::$routes[0];

        $this->assertSame('PUT', $route['method']);
        $this->assertSame('/update', $route['route']);
    }

    public function testDeleteAddsRoute(): void
    {
        Route::delete('remove', 'DeleteController@destroy');
        $route = Route::$routes[0];

        $this->assertSame('DELETE', $route['method']);
        $this->assertSame('/remove', $route['route']);
    }

    public function testMatchAddsMultipleMethods(): void
    {
        Route::match(['GET', 'POST'], 'multi', 'MultiController@handle');
        $this->assertCount(2, Route::$routes);

        $this->assertSame('GET', Route::$routes[0]['method']);
        $this->assertSame('POST', Route::$routes[1]['method']);
    }

    public function testGroupWithPrefixAndMiddleware(): void
    {
        Route::group(['prefix' => 'api', 'middleware' => 'auth'], static function () {
            Route::get('users', 'UserController@index');
        });

        $route = Route::$routes[0];

        $this->assertSame('/api/users', $route['route']);
        $this->assertContains('auth', $route['middlewares']);
    }

    public function testRouteWithMiddlewareOption(): void
    {
        Route::get('settings', 'SettingsController@index', ['middleware' => ['admin']]);

        $route = Route::$routes[0];
        $this->assertContains('admin', $route['middlewares']);
    }

    public function testResourceWithOnly(): void
    {
        Route::resource('posts', 'PostController', ['only' => ['index', 'show']]);

        $this->assertCount(2, Route::$routes);

        $this->assertSame('/posts', Route::$routes[0]['route']);
        $this->assertSame([ 'PostController', 'index' ], Route::$routes[0]['action']);

        $this->assertSame('/posts/{id}', Route::$routes[1]['route']);
        $this->assertSame([ 'PostController', 'show' ], Route::$routes[1]['action']);
    }

    public function testResourceWithExcept(): void
    {
        Route::resource('users', 'UserController', ['except' => ['edit', 'create']]);

        $routes = Route::$routes;

        $routeNames = array_map(static fn ($r) => $r['action'][1], $routes);

        $this->assertNotContains('edit', $routeNames);
        $this->assertNotContains('create', $routeNames);
        $this->assertContains('index', $routeNames);
        $this->assertContains('destroy', $routeNames);
    }
}
