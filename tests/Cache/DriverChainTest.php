<?php

declare(strict_types=1);

namespace Asterios\Test\Cache;

use Asterios\Core\Contracts\Utilities\Cache\CacheDriverInterface;
use Asterios\Core\Contracts\Utilities\Cache\LockInterface;
use Asterios\Core\Utilities\Cache\DriverChain;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

final class DriverChainTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_get_returns_value_from_primary_driver(): void
    {
        $primary = m::mock(CacheDriverInterface::class);
        $secondary = m::mock(CacheDriverInterface::class);

        $primary->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn('bar');

        $chain = new DriverChain([
            $primary,
            $secondary,
        ]);

        self::assertSame('bar', $chain->get('foo'));
    }

    public function test_get_falls_back_to_secondary_and_warms_primary(): void
    {
        $primary = m::mock(CacheDriverInterface::class);
        $secondary = m::mock(CacheDriverInterface::class);

        $primary->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn(null);

        $secondary->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn('bar');

        $primary->shouldReceive('set')
            ->once()
            ->with('foo', 'bar', null)
            ->andReturn(true);

        $chain = new DriverChain([
            $primary,
            $secondary,
        ]);

        self::assertSame('bar', $chain->get('foo'));
    }

    public function test_set_writes_to_all_drivers(): void
    {
        $primary = m::mock(CacheDriverInterface::class);
        $secondary = m::mock(CacheDriverInterface::class);

        $primary->shouldReceive('set')
            ->once()
            ->with('foo', 'bar', 300)
            ->andReturn(true);

        $secondary->shouldReceive('set')
            ->once()
            ->with('foo', 'bar', 300)
            ->andReturn(true);

        $chain = new DriverChain([
            $primary,
            $secondary,
        ]);

        self::assertTrue(
            $chain->set('foo', 'bar', 300)
        );
    }

    public function test_increment_uses_primary_driver_only(): void
    {
        $primary = m::mock(CacheDriverInterface::class);
        $secondary = m::mock(CacheDriverInterface::class);

        $primary->shouldReceive('increment')
            ->once()
            ->with('counter', 1)
            ->andReturn(2);

        $chain = new DriverChain([
            $primary,
            $secondary,
        ]);

        self::assertSame(
            2,
            $chain->increment('counter')
        );
    }

    public function test_lock_uses_primary_driver_only(): void
    {
        $primary = m::mock(CacheDriverInterface::class);
        $secondary = m::mock(CacheDriverInterface::class);
        $lock = m::mock(LockInterface::class);

        $primary->shouldReceive('lock')
            ->once()
            ->with('job', 10)
            ->andReturn($lock);

        $chain = new DriverChain([
            $primary,
            $secondary,
        ]);

        self::assertSame(
            $lock,
            $chain->lock('job')
        );
    }

    public function test_has_returns_true_when_any_driver_contains_key(): void
    {
        $primary = m::mock(CacheDriverInterface::class);
        $secondary = m::mock(CacheDriverInterface::class);

        $primary->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(false);

        $secondary->shouldReceive('has')
            ->once()
            ->with('foo')
            ->andReturn(true);

        $chain = new DriverChain([
            $primary,
            $secondary,
        ]);

        self::assertTrue($chain->has('foo'));
    }

    public function test_warmup_preserves_known_ttl(): void
    {
        $primary = m::mock(CacheDriverInterface::class);
        $secondary = m::mock(CacheDriverInterface::class);

        $primary->shouldReceive('set')
            ->twice()
            ->with('foo', 'bar', 300)
            ->andReturn(true);

        $secondary->shouldReceive('set')
            ->once()
            ->with('foo', 'bar', 300)
            ->andReturn(true);

        $primary->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn(null);

        $secondary->shouldReceive('get')
            ->once()
            ->with('foo')
            ->andReturn('bar');

        $chain = new DriverChain([
            $primary,
            $secondary,
        ]);

        $chain->set('foo', 'bar', 300);

        self::assertSame(
            'bar',
            $chain->get('foo')
        );
    }
}
