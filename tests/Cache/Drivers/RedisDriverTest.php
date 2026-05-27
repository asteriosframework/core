<?php

declare(strict_types=1);

namespace Asterios\Test\Cache\Drivers;

use Asterios\Core\Utilities\Cache\Drivers\RedisDriver;
use Asterios\Core\Utilities\Cache\Serialization\JsonSerializer;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Redis;
use RedisException;

final class RedisDriverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_get_returns_null_when_key_is_missing(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('get')
            ->once()
            ->with('asterios:foo')
            ->andReturn(false);

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertNull(
            $driver->get('foo')
        );
    }

    public function test_get_returns_unserialized_value(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('get')
            ->once()
            ->with('asterios:foo')
            ->andReturn(json_encode('bar'));

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertSame(
            'bar',
            $driver->get('foo')
        );
    }

    public function test_set_without_ttl_uses_set(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('set')
            ->once()
            ->with(
                'asterios:foo',
                json_encode('bar')
            )
            ->andReturn(true);

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertTrue(
            $driver->set('foo', 'bar')
        );
    }

    public function test_set_with_ttl_uses_setex(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('setex')
            ->once()
            ->with(
                'asterios:foo',
                300,
                json_encode('bar')
            )
            ->andReturn(true);

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertTrue(
            $driver->set('foo', 'bar', 300)
        );
    }

    public function test_delete_removes_key(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('del')
            ->once()
            ->with('asterios:foo')
            ->andReturn(1);

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertTrue(
            $driver->delete('foo')
        );
    }

    public function test_increment_uses_native_redis_increment(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('incrBy')
            ->once()
            ->with('asterios:counter', 5)
            ->andReturn(10);

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertSame(
            10,
            $driver->increment('counter', 5)
        );
    }

    public function test_decrement_uses_native_redis_decrement(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('decrBy')
            ->once()
            ->with('asterios:counter', 2)
            ->andReturn(8);

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertSame(
            8,
            $driver->decrement('counter', 2)
        );
    }

    public function test_add_without_ttl_uses_nx_option(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('set')
            ->once()
            ->with(
                'asterios:foo',
                json_encode('bar'),
                ['nx']
            )
            ->andReturn(true);

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertTrue(
            $driver->add('foo', 'bar')
        );
    }

    public function test_add_with_ttl_uses_nx_and_ex_options(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('set')
            ->once()
            ->with(
                'asterios:foo',
                json_encode('bar'),
                [
                    'nx',
                    'ex' => 300,
                ]
            )
            ->andReturn(true);

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertTrue(
            $driver->add('foo', 'bar', 300)
        );
    }

    public function test_get_multiple_returns_unserialized_values(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('mget')
            ->once()
            ->with([
                'asterios:a',
                'asterios:b',
            ])
            ->andReturn([
                json_encode('one'),
                json_encode('two'),
            ]);

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        $result = $driver->getMultiple(['a', 'b']);

        self::assertSame([
            'a' => 'one',
            'b' => 'two',
        ], $result);
    }

    public function test_set_multiple_without_ttl_uses_mset(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('mset')
            ->once()
            ->with([
                'asterios:a' => json_encode('one'),
                'asterios:b' => json_encode('two'),
            ])
            ->andReturn(true);

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertTrue(
            $driver->setMultiple([
                'a' => 'one',
                'b' => 'two',
            ])
        );
    }

    public function test_is_available_returns_true_when_ping_succeeds(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('ping')
            ->once()
            ->andReturn('+PONG');

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertTrue(
            $driver->isAvailable()
        );
    }

    public function test_is_available_returns_false_on_redis_exception(): void
    {
        $redis = Mockery::mock(Redis::class);

        $redis->shouldReceive('ping')
            ->once()
            ->andThrow(new RedisException('fail'));

        $driver = new RedisDriver(
            redis: $redis,
            serializer: new JsonSerializer(),
        );

        self::assertFalse(
            $driver->isAvailable()
        );
    }
}