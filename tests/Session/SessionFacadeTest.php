<?php declare(strict_types=1);

namespace Asterios\Test\Session;

use Asterios\Core\Session;
use Asterios\Core\Session\FlashBag;
use Asterios\Core\Session\SessionManager;
use Asterios\Core\Session\Store\ArraySessionStore;
use Asterios\Core\Session\Store\ExpiringStore;
use PHPUnit\Framework\TestCase;

final class SessionFacadeTest extends TestCase
{
    protected function setUp(): void
    {
        Session::reset();

        Session::setInstance(
            new SessionManager(
                new ArraySessionStore(),
                null,
                new FlashBag(),
                new ExpiringStore(),
            )
        );
    }

    protected function tearDown(): void
    {
        Session::reset();
    }

    public function testLegacySetAndGetStillWork(): void
    {
        Session::set('user.name', 'Chris');

        self::assertSame('Chris', Session::get('user.name'));
    }

    public function testHasAndRemove(): void
    {
        Session::set('temp.key', 'x');

        self::assertTrue(Session::has('temp.key'));

        Session::remove('temp.key');

        self::assertFalse(Session::has('temp.key'));
    }

    public function testTypedAccessors(): void
    {
        Session::set('age', 42);
        Session::set('enabled', true);
        Session::set('price', 9.99);
        Session::set('cart', ['foo' => 'bar']);

        self::assertSame(42, Session::getInt('age'));
        self::assertTrue(Session::getBool('enabled'));
        self::assertSame(9.99, Session::getFloat('price'));
        self::assertSame(['foo' => 'bar'], Session::getArray('cart'));
    }

    public function testFlashFacadeApi(): void
    {
        Session::flash('success', 'Saved');

        self::assertTrue(Session::hasFlash('success'));
        self::assertSame('Saved', Session::getFlash('success'));
    }

    public function testTtlFacadeApi(): void
    {
        Session::putWithTtl('otp.code', '123456', 60);

        self::assertSame('123456', Session::get('otp.code'));
        self::assertFalse(Session::hasExpired('otp.code'));
    }

    public function testInvalidateFacadeApi(): void
    {
        Session::set('auth.user', 123);

        Session::invalidate();

        self::assertFalse(Session::has('auth.user'));
    }

    public function testPullFacadeApi(): void
    {
        Session::set('flash.temp', 'hello');

        self::assertSame('hello', Session::pull('flash.temp'));
        self::assertFalse(Session::has('flash.temp'));
    }
}
