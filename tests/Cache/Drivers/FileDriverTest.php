<?php

declare(strict_types=1);

namespace Asterios\Test\Cache\Drivers;

use Asterios\Core\Utilities\Cache\Drivers\FileDriver;
use Asterios\Core\Utilities\Cache\Serialization\JsonSerializer;
use PHPUnit\Framework\TestCase;

final class FileDriverTest extends TestCase
{
    private string $cachePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePath = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'asterios-cache-test-'
            . uniqid('', true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->cachePath);

        parent::tearDown();
    }

    public function test_set_and_get_value(): void
    {
        $driver = $this->makeDriver();

        self::assertTrue(
            $driver->set('foo', 'bar')
        );

        self::assertSame(
            'bar',
            $driver->get('foo')
        );
    }

    public function test_get_returns_null_for_missing_key(): void
    {
        $driver = $this->makeDriver();

        self::assertNull(
            $driver->get('missing')
        );
    }

    public function test_expired_value_returns_null(): void
    {
        $driver = $this->makeDriver();

        $driver->set('temp', 'value', 1);

        sleep(2);

        self::assertNull(
            $driver->get('temp')
        );
    }

    public function test_delete_removes_key(): void
    {
        $driver = $this->makeDriver();

        $driver->set('foo', 'bar');

        self::assertTrue(
            $driver->delete('foo')
        );

        self::assertNull(
            $driver->get('foo')
        );
    }

    public function test_clear_removes_all_cached_entries(): void
    {
        $driver = $this->makeDriver();

        $driver->set('a', 'one');
        $driver->set('b', 'two');

        self::assertTrue(
            $driver->clear()
        );

        self::assertNull($driver->get('a'));
        self::assertNull($driver->get('b'));
    }

    public function test_increment_creates_new_counter(): void
    {
        $driver = $this->makeDriver();

        self::assertSame(
            1,
            $driver->increment('counter')
        );
    }

    public function test_increment_existing_counter(): void
    {
        $driver = $this->makeDriver();

        $driver->set('counter', 10);

        self::assertSame(
            15,
            $driver->increment('counter', 5)
        );
    }

    public function test_decrement_existing_counter(): void
    {
        $driver = $this->makeDriver();

        $driver->set('counter', 10);

        self::assertSame(
            7,
            $driver->decrement('counter', 3)
        );
    }

    public function test_increment_returns_false_for_non_numeric_values(): void
    {
        $driver = $this->makeDriver();

        $driver->set('foo', 'bar');

        self::assertFalse(
            $driver->increment('foo')
        );
    }

    public function test_add_only_writes_when_key_does_not_exist(): void
    {
        $driver = $this->makeDriver();

        self::assertTrue(
            $driver->add('foo', 'bar')
        );

        self::assertFalse(
            $driver->add('foo', 'baz')
        );

        self::assertSame(
            'bar',
            $driver->get('foo')
        );
    }

    public function test_get_multiple_returns_expected_values(): void
    {
        $driver = $this->makeDriver();

        $driver->setMultiple([
            'a' => 'one',
            'b' => 'two',
        ]);

        self::assertSame([
            'a' => 'one',
            'b' => 'two',
            'c' => null,
        ], $driver->getMultiple([
            'a',
            'b',
            'c',
        ]));
    }

    public function test_delete_multiple_removes_all_keys(): void
    {
        $driver = $this->makeDriver();

        $driver->setMultiple([
            'a' => 'one',
            'b' => 'two',
        ]);

        self::assertTrue(
            $driver->deleteMultiple([
                'a',
                'b',
            ])
        );

        self::assertNull($driver->get('a'));
        self::assertNull($driver->get('b'));
    }

    public function test_is_available_returns_true_for_writable_directory(): void
    {
        mkdir($this->cachePath, 0775, true);

        $driver = $this->makeDriver();

        self::assertTrue(
            $driver->isAvailable()
        );
    }

    private function makeDriver(): FileDriver
    {
        return new FileDriver(
            cachePath: $this->cachePath,
            serializer: new JsonSerializer(),
        );
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path))
        {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file)
        {
            if ($file->isDir())
            {
                rmdir($file->getPathname());
            }
            else
            {
                unlink($file->getPathname());
            }
        }

        rmdir($path);
    }
}
