<?php declare(strict_types=1);

namespace Asterios\Test\Session;

use Asterios\Core\Config\SessionConfig;
use Asterios\Core\Session\FlashBag;
use Asterios\Core\Session\SessionManager;
use Asterios\Core\Session\Store\ArraySessionStore;
use Asterios\Core\Session\Store\ExpiringStore;
use PHPUnit\Framework\TestCase;

final class SessionManagerTest extends TestCase
{
    private SessionManager $session;

    protected function setUp(): void
    {
        $this->session = new SessionManager(
            new ArraySessionStore(),
            new SessionConfig(namespace: 'test'),
            new FlashBag(),
            new ExpiringStore(),
        );
    }

    public function testSetAndGetValue(): void
    {
        $this->session->set('user.name', 'Chris');

        self::assertSame('Chris', $this->session->get('user.name'));
    }

    public function testBulkSetAndBulkGet(): void
    {
        $this->session->set([
            'foo' => 'bar',
            'baz' => 123,
        ]);

        self::assertSame(
            [
                'foo' => 'bar',
                'baz' => 123,
            ],
            $this->session->get(['foo', 'baz'])
        );
    }

    public function testHasDetectsValue(): void
    {
        $this->session->set('exists', true);

        self::assertTrue($this->session->has('exists'));
    }

    public function testRemoveDeletesValue(): void
    {
        $this->session->set('temp.value', 'x');
        $this->session->remove('temp.value');

        self::assertFalse($this->session->has('temp.value'));
    }

    public function testPullReturnsAndRemovesValue(): void
    {
        $this->session->set('flash.temp', 'hello');

        self::assertSame('hello', $this->session->pull('flash.temp'));
        self::assertFalse($this->session->has('flash.temp'));
    }

    public function testTypedAccessors(): void
    {
        $this->session->set('name', 'Chris');
        $this->session->set('age', 42);
        $this->session->set('price', 12.5);
        $this->session->set('enabled', true);
        $this->session->set('cart', ['a' => 1]);

        self::assertSame('Chris', $this->session->getString('name'));
        self::assertSame(42, $this->session->getInt('age'));
        self::assertSame(12.5, $this->session->getFloat('price'));
        self::assertTrue($this->session->getBool('enabled'));
        self::assertSame(['a' => 1], $this->session->getArray('cart'));
    }

    public function testFlashLifecycle(): void
    {
        $this->session->flash('success', 'Saved');

        self::assertSame('Saved', $this->session->getFlash('success'));
    }

    public function testTtlStorage(): void
    {
        $this->session->putWithTtl('otp.code', '123456', 60);

        self::assertSame('123456', $this->session->get('otp.code'));
        self::assertFalse($this->session->hasExpired('otp.code'));
    }

    public function testClearRemovesUserPayload(): void
    {
        $this->session->set('foo', 'bar');

        $this->session->clear();

        self::assertFalse($this->session->has('foo'));

        self::assertSame([], $this->session->all());
    }

    public function testInvalidateClearsSession(): void
    {
        $this->session->set('auth.user', 1);

        $this->session->invalidate();

        self::assertSame([], $this->session->all());
    }
}
