<?php declare(strict_types=1);

namespace Asterios\Test\Session\Support;

use Asterios\Core\Session\Support\Arr;
use PHPUnit\Framework\TestCase;

final class ArrTest extends TestCase
{
    public function testGetReturnsNestedValue(): void
    {
        $data = [
            'user' => [
                'profile' => [
                    'name' => 'Chris',
                ],
            ],
        ];

        self::assertSame('Chris', Arr::get($data, 'user.profile.name'));
    }

    public function testGetReturnsDefaultWhenMissing(): void
    {
        $data = [];

        self::assertSame('fallback', Arr::get($data, 'missing.key', 'fallback'));
    }

    public function testBulkGetReturnsMultipleValues(): void
    {
        $data = [
            'foo' => 'bar',
            'baz' => 123,
        ];

        self::assertSame(
            [
                'foo' => 'bar',
                'baz' => 123,
            ],
            Arr::get($data, ['foo', 'baz'])
        );
    }

    public function testSetCreatesNestedStructure(): void
    {
        $data = [];

        Arr::set($data, 'cart.items.count', 3);

        self::assertSame(3, $data['cart']['items']['count']);
    }

    public function testBulkSetStoresMultipleValues(): void
    {
        $data = [];

        Arr::set($data, [
            'name' => 'Chris',
            'age' => 42,
        ]);

        self::assertSame('Chris', $data['name']);
        self::assertSame(42, $data['age']);
    }

    public function testHasDetectsExistingValue(): void
    {
        $data = [
            'settings' => [
                'theme' => 'dark',
            ],
        ];

        self::assertTrue(Arr::has($data, 'settings.theme'));
    }

    public function testHasReturnsFalseWhenMissing(): void
    {
        self::assertFalse(Arr::has([], 'missing.value'));
    }

    public function testForgetRemovesNestedValue(): void
    {
        $data = [
            'user' => [
                'email' => 'test@example.com',
            ],
        ];

        Arr::forget($data, 'user.email');

        self::assertArrayNotHasKey('email', $data['user']);
    }
}
