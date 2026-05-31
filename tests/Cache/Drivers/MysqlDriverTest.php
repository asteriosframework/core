<?php

declare(strict_types=1);

namespace Asterios\Test\Cache\Drivers;

use Asterios\Core\Contracts\Utilities\Cache\LockInterface;
use Asterios\Core\Db;
use Asterios\Core\Utilities\Cache\Drivers\MysqlDriver;
use Asterios\Core\Utilities\Cache\Serialization\JsonSerializer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class MysqlDriverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private mixed $dbMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbMock = m::mock(
            'alias:' . Db::class
        );
    }
    public function test_get_returns_null_when_key_is_missing(): void
    {
        $this->dbMock->shouldReceive('quote')
            ->andReturn("'asterios:foo'");

        $this->dbMock->shouldReceive('read')
            ->once()
            ->andReturn([]);

        $driver = $this->makeDriver();

        self::assertNull(
            $driver->get('foo')
        );
    }

    public function test_get_returns_cached_value(): void
    {
        $payload = json_encode([
            'value' => 'bar',
            'expires_at' => null,
        ]);

        $this->dbMock->shouldReceive('quote')
            ->andReturn("'asterios:foo'");

        $this->dbMock->shouldReceive('read')
            ->once()
            ->andReturn([
                [
                    'cache_value' => $payload,
                    'expires_at' => null,
                ],
            ]);

        $driver = $this->makeDriver();

        self::assertSame(
            'bar',
            $driver->get('foo')
        );
    }

    public function test_get_returns_null_for_expired_entry(): void
    {
        $this->dbMock->shouldReceive('quote')
            ->andReturn("'asterios:foo'");

        $this->dbMock->shouldReceive('read')
            ->once()
            ->andReturn([
                [
                    'cache_value' => json_encode([
                        'value' => 'bar',
                        'expires_at' => time() - 100,
                    ]),
                    'expires_at' => time() - 100,
                ],
            ]);

        $this->dbMock->shouldReceive('write')
            ->once()
            ->andReturn(true);

        $driver = $this->makeDriver();

        self::assertNull(
            $driver->get('foo')
        );
    }

    public function test_set_writes_cache_entry(): void
    {
        $this->dbMock->shouldReceive('quote')
            ->andReturnUsing(
                static fn ($value) => "'" . $value . "'"
            );

        $this->dbMock->shouldReceive('write')
            ->once()
            ->andReturn(true);

        $driver = $this->makeDriver();

        self::assertTrue(
            $driver->set('foo', 'bar', 300)
        );
    }

    public function test_delete_removes_entry(): void
    {
        $this->dbMock->shouldReceive('quote')
            ->andReturn("'asterios:foo'");

        $this->dbMock->shouldReceive('write')
            ->once()
            ->andReturn(true);

        $driver = $this->makeDriver();

        self::assertTrue(
            $driver->delete('foo')
        );
    }

    public function test_clear_truncates_table(): void
    {
        $this->dbMock->shouldReceive('write')
            ->once()
            ->andReturn(true);

        $driver = $this->makeDriver();

        self::assertTrue(
            $driver->clear()
        );
    }

    public function test_increment_creates_new_counter(): void
    {
        $this->dbMock->shouldReceive('quote')
            ->andReturn("'asterios:counter'");

        $this->dbMock->shouldReceive('read')
            ->once()
            ->andReturn([]);

        $this->dbMock->shouldReceive('write')
            ->once()
            ->andReturn(true);

        $driver = m::mock(
            MysqlDriver::class,
            [
                'default',
                'cache_entries',
                new JsonSerializer(),
            ]
        )->makePartial();

        $lock = m::mock(
            LockInterface::class
        );

        $lock->shouldReceive('isAcquired')
            ->once()
            ->andReturn(true);

        $lock->shouldReceive('release')
            ->once();

        $driver->shouldReceive('lock')
            ->once()
            ->andReturn($lock);

        self::assertSame(
            1,
            $driver->increment('counter')
        );
    }

    public function test_add_returns_false_when_key_exists(): void
    {
        $payload = json_encode([
            'value' => 'existing',
            'expires_at' => null,
        ]);

        $this->dbMock->shouldReceive('quote')
            ->andReturn("'asterios:foo'");

        $this->dbMock->shouldReceive('read')
            ->once()
            ->andReturn([
                [
                    'cache_value' => $payload,
                    'expires_at' => null,
                ],
            ]);

        $driver = $this->makeDriver();

        self::assertFalse(
            $driver->add('foo', 'bar')
        );
    }

    public function test_is_available_returns_true_when_database_responds(): void
    {
        $this->dbMock->shouldReceive('read')
            ->once()
            ->with('SELECT 1', 'default')
            ->andReturn([['1' => 1]]);

        $driver = $this->makeDriver();

        self::assertTrue(
            $driver->isAvailable()
        );
    }

    public function test_is_available_returns_false_on_exception(): void
    {
        $this->dbMock->shouldReceive('read')
            ->once()
            ->andThrow(new RuntimeException('db down'));

        $driver = $this->makeDriver();

        self::assertFalse(
            $driver->isAvailable()
        );
    }

    private function makeDriver(): MysqlDriver
    {
        return new MysqlDriver(
            configGroup: 'default',
            table: 'cache_entries',
            serializer: new JsonSerializer(),
        );
    }
}
