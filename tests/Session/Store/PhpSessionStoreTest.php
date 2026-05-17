<?php declare(strict_types=1);

namespace Asterios\Test\Session\Store;

use Asterios\Core\Session\Store\PhpSessionStore;
use PHPUnit\Framework\TestCase;

final class PhpSessionStoreTest extends TestCase
{
    private PhpSessionStore $store;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            session_unset();
            session_destroy();
        }

        $this->store = new PhpSessionStore();
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            session_unset();
            session_destroy();
        }

        $_SESSION = [];
    }

    public function testStartStartsSession(): void
    {
        self::assertFalse($this->store->isStarted());

        $this->store->start();

        self::assertTrue($this->store->isStarted());
    }

    public function testRootReturnsSessionReference(): void
    {
        $root = &$this->store->root();

        $root['foo'] = 'bar';

        self::assertSame('bar', $_SESSION['foo']);
    }

    public function testRegenerateReturnsTrue(): void
    {
        $this->store->start();

        self::assertTrue(
            $this->store->regenerate()
        );
    }

    public function testDestroyClearsSession(): void
    {
        $this->store->start();

        $_SESSION['user'] = [
            'id' => 1,
        ];

        $this->store->destroy();

        self::assertSame([], $_SESSION);
    }

    public function testDestroyWithoutStartedSessionDoesNothing(): void
    {
        self::assertFalse($this->store->isStarted());

        $this->store->destroy();

        self::assertFalse($this->store->isStarted());
    }
}