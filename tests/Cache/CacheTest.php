<?php

declare(strict_types=1);

namespace Asterios\Test\Cache;

use Asterios\Core\Contracts\Utilities\Cache\CacheDriverInterface;
use Asterios\Core\Contracts\Utilities\Cache\LockInterface;
use Asterios\Core\Utilities\Cache\Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

final class CacheTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_get_returns_default_when_key_is_missing(): void
    {
        $driver = Mockery::mock(CacheDriverInterface::class);

        $driver->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn(null);

        $cache = new Cache($driver);

        self::assertSame(
            'fallback',
            $cache->get('foo', 'fallback')
        );
    }

    public function test_set_uses_default_ttl_when_none_is_provided(): void
    {
        $driver = Mockery::mock(CacheDriverInterface::class);

        $driver->shouldReceive('set')
            ->once()
            ->with('foo', 'bar', 3600)
            ->andReturn(true);

        $cache = new Cache($driver, 3600);

        self::assertTrue(
            $cache->set('foo', 'bar')
        );
    }

    public function test_set_uses_explicit_ttl_when_provided(): void
    {
        $driver = Mockery::mock(CacheDriverInterface::class);

        $driver->shouldReceive('set')
            ->once()
            ->with('foo', 'bar', 120)
            ->andReturn(true);

        $cache = new Cache($driver, 3600);

        self::assertTrue(
            $cache->set('foo', 'bar', 120)
        );
    }

    public function test_remember_returns_existing_cached_value(): void
    {
        $driver = Mockery::mock(CacheDriverInterface::class);

        $driver->shouldReceive('get')
            ->once()
            ->with('users')
            ->andReturn(['cached']);

        $cache = new Cache($driver);

        $result = $cache->remember(
            'users',
            fn () => ['fresh']
        );

        self::assertSame(['cached'], $result);
    }

    public function test_remember_executes_callback_and_stores_value_on_cache_miss(): void
    {
        $driver = Mockery::mock(CacheDriverInterface::class);

        $driver->shouldReceive('get')
            ->once()
            ->with('users')
            ->andReturn(null);

        $driver->shouldReceive('set')
            ->once()
            ->with('users', ['fresh'], 3600)
            ->andReturn(true);

        $cache = new Cache($driver, 3600);

        $result = $cache->remember(
            'users',
            fn () => ['fresh']
        );

        self::assertSame(['fresh'], $result);
    }

    public function test_remember_with_lock_uses_cache_lock(): void
    {
        $driver = Mockery::mock(CacheDriverInterface::class);
        $lock = Mockery::mock(LockInterface::class);

        $driver->shouldReceive('get')
            ->twice()
            ->with('report')
            ->andReturn(null, null);

        $driver->shouldReceive('lock')
            ->once()
            ->with('remember:report', 10)
            ->andReturn($lock);

        $lock->shouldReceive('isAcquired')
            ->once()
            ->andReturn(true);

        $lock->shouldReceive('release')
            ->once();

        $driver->shouldReceive('set')
            ->once()
            ->with('report', 'generated', 3600)
            ->andReturn(true);

        $cache = new Cache($driver);

        $result = $cache->remember(
            'report',
            fn () => 'generated',
            lock: true
        );

        self::assertSame('generated', $result);
    }

    public function test_get_or_set_returns_existing_value_when_present(): void
    {
        $driver = Mockery::mock(CacheDriverInterface::class);

        $driver->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn('existing');

        $cache = new Cache($driver);

        $result = $cache->getOrSet('foo', 'new');

        self::assertSame('existing', $result);
    }

    public function test_get_or_set_uses_atomic_add_when_missing(): void
    {
        $driver = Mockery::mock(CacheDriverInterface::class);

        $driver->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn(null);

        $driver->shouldReceive('add')
            ->once()
            ->with('foo', 'bar', 3600)
            ->andReturn(true);

        $cache = new Cache($driver);

        $result = $cache->getOrSet('foo', 'bar');

        self::assertSame('bar', $result);
    }

    public function test_tags_are_applied_to_cache_keys(): void
    {
        $driver = Mockery::mock(CacheDriverInterface::class);

        $driver->shouldReceive('getTagVersion')
            ->once()
            ->with('users')
            ->andReturn(5);

        $driver->shouldReceive('set')
            ->once()
            ->with('users:5|list', ['x'], 3600)
            ->andReturn(true);

        $cache = new Cache($driver);

        self::assertTrue(
            $cache->tags(['users'])->set('list', ['x'])
        );
    }
}