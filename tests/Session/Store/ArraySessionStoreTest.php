<?php declare(strict_types=1);

namespace Asterios\Test\Session\Store;

use Asterios\Core\Session\Store\ArraySessionStore;
use PHPUnit\Framework\TestCase;

final class ArraySessionStoreTest extends TestCase
{
    public function testStartMarksStoreAsStarted(): void
    {
        $store = new ArraySessionStore();

        $store->start();

        self::assertTrue($store->isStarted());
    }

    public function testRootReturnsPersistentReference(): void
    {
        $store = new ArraySessionStore();

        $root = &$store->root();
        $root['foo'] = 'bar';

        $secondRoot = &$store->root();

        self::assertSame('bar', $secondRoot['foo']);
    }

    public function testRegenerateChangesSessionId(): void
    {
        $store = new ArraySessionStore();

        $before = $store->sessionId();

        $store->regenerate(true);

        $after = $store->sessionId();

        self::assertNotSame($before, $after);
    }

    public function testDestroyResetsStore(): void
    {
        $store = new ArraySessionStore();

        $store->start();

        $root = &$store->root();
        $root['user'] = ['id' => 1];

        $store->destroy();

        self::assertFalse($store->isStarted());
        self::assertSame([], $store->root());
        self::assertSame('array-session', $store->sessionId());
    }
}