<?php
declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\Exception\RouterException;
use Asterios\Core\Router;
use Asterios\Core\Support\Route;
use Asterios\Test\Stubs\CategoriesController;
use Asterios\Test\Stubs\IndexController;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset Routes & $_SERVER
        Route::$routes = [];
        $_SERVER = [];

        // Beispielrouten wie aus routes.php
        Route::group(['prefix' => 'v1', 'middleware' => ['auth']], static function () {
            Route::get('/', [IndexController::class, 'index'], ['middleware' => ['version']]);
            Route::get('categories', [CategoriesController::class, 'index']);
        });
    }

    public function testNamespaceSetterGetter(): void
    {
        $router = new Router();
        $router->setNamespace('App\\Http\\Controllers');
        $this->assertSame('App\\Http\\Controllers', $router->getNamespace());
    }

    public function testMiddlewareNamespaceSetterGetter(): void
    {
        $router = new Router();
        $router->setMiddlewareNamespace('Asterios\\Test\\Stubs');
        $this->assertSame('Asterios\\Test\\Stubs', $router->getMiddlewareNamespace());
    }

    public function testRequestMethodGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $router = new Router();
        $this->assertSame('GET', $router->getRequestMethod());
    }

    public function testRequestMethodOverride(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $router = $this->getMockBuilder(Router::class)
            ->onlyMethods(['getRequestHeaders'])
            ->getMock();

        $router->method('getRequestHeaders')->willReturn([
            'X-HTTP-Method-Override' => 'PUT',
        ]);

        $this->assertSame('PUT', $router->getRequestMethod());
    }

    public function testCurrentUriParsing(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/v1/categories?foo=bar';

        $router = new Router();
        $this->assertSame('/v1/categories', $router->getCurrentUri());
    }

    public function testSuccessfulRouteExecution(): void
    {
        require_once __DIR__ . '/Stubs/AuthMiddleware.php';
        require_once __DIR__ . '/Stubs/VersionMiddleware.php';
        require_once __DIR__ . '/Stubs/IndexController.php';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/v1';

        $router = new Router();
        $router->setMiddlewareNamespace('Asterios\\Test\\Stubs');

        ob_start();
        $success = $router->run();
        $output = ob_get_clean();

        $this->assertTrue($success);
        $this->assertSame('IndexController@index', trim($output));
    }

    public function testRouteReturns404(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/not-found';

        $router = new Router();
        $router->setMiddlewareNamespace('Asterios\\Test\\Stubs');

        ob_start();
        $success = $router->run();
        ob_end_clean();

        $this->assertFalse($success);
        $this->assertSame(404, http_response_code());
    }

    public function testInvalidMiddlewareThrowsException(): void
    {
        Route::$routes[] = [
            'method' => 'GET',
            'route' => '/fail',
            'action' => fn () => null,
            'middlewares' => ['doesNotExist'],
        ];

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/fail';

        $router = new Router();
        $router->setMiddlewareNamespace('Asterios\\Test\\Stubs');

        $this->expectException(RouterException::class);
        $router->run();
    }

    public function testRequestMethodHeadStripsOutput(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $_SERVER['REQUEST_URI'] = '/v1';

        $router = new Router();
        $router->setMiddlewareNamespace('Asterios\\Test\\Stubs');

        ob_start();
        $result = $router->run();
        $output = ob_get_clean();

        $this->assertTrue($result);
        $this->assertEmpty($output);
    }
}