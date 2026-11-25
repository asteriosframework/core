<?php declare(strict_types=1);

use Asterios\Core\Contracts\Http\MiddlewareInterface;

test('middleware interface accepts true return value', function (): void {
    $middleware = new class () implements MiddlewareInterface {
        public function handle(): bool
        {
            return true;
        }
    };

    expect($middleware->handle())->toBeTrue();
});

test('middleware interface accepts false return value', function (): void {
    $middleware = new class () implements MiddlewareInterface {
        public function handle(): bool
        {
            return false;
        }
    };

    expect($middleware->handle())->toBeFalse();
});
