<?php declare(strict_types=1);

namespace Asterios\Test\Pest\Unit\Contracts\Http;

use Asterios\Core\Contracts\Http\MiddlewareInterface;

test('legacy middleware works without framework interface', function (): void {
    $middleware = new class () {
        public function handle(): bool
        {
            return true;
        }
    };

    expect($middleware)->not->toBeInstanceOf(
        MiddlewareInterface::class
    )
        ->and($middleware->handle())
        ->toBeTrue();
});