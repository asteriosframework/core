<?php declare(strict_types=1);

namespace Asterios\Test\Session;

use Asterios\Core\Session\FlashBag;
use PHPUnit\Framework\TestCase;

final class FlashBagTest extends TestCase
{
    private FlashBag $flashBag;

    /**
     * @var array<string, mixed>
     */
    private array $session;

    protected function setUp(): void
    {
        $this->flashBag = new FlashBag();
        $this->session = [];
    }

    public function testFlashStoresValue(): void
    {
        $this->flashBag->flash($this->session, 'success', 'Saved');

        self::assertSame('Saved', $this->flashBag->get($this->session, 'success'));
    }

    public function testHasDetectsFlashValue(): void
    {
        $this->flashBag->flash($this->session, 'notice', 'Hello');

        self::assertTrue($this->flashBag->has($this->session, 'notice'));
    }

    public function testGetReturnsDefaultWhenMissing(): void
    {
        self::assertSame('fallback', $this->flashBag->get($this->session, 'missing', 'fallback'));
    }

    public function testAgeExpiresFlashAfterSecondCycle(): void
    {
        $this->flashBag->flash($this->session, 'success', 'Saved');

        $this->flashBag->age($this->session);
        self::assertSame('Saved', $this->flashBag->get($this->session, 'success'));

        $this->flashBag->age($this->session);
        self::assertNull($this->flashBag->get($this->session, 'success'));
    }

    public function testKeepPreservesSpecificFlashValue(): void
    {
        $this->flashBag->flash($this->session, 'warning', 'Careful');

        $this->flashBag->age($this->session);
        $this->flashBag->keep($this->session, 'warning');
        $this->flashBag->age($this->session);

        self::assertSame('Careful', $this->flashBag->get($this->session, 'warning'));
    }

    public function testReflashPreservesAllFlashValues(): void
    {
        $this->flashBag->flash($this->session, 'a', 'one');
        $this->flashBag->flash($this->session, 'b', 'two');

        $this->flashBag->age($this->session);
        $this->flashBag->reflash($this->session);
        $this->flashBag->age($this->session);

        self::assertSame('one', $this->flashBag->get($this->session, 'a'));
        self::assertSame('two', $this->flashBag->get($this->session, 'b'));
    }

    public function testClearRemovesAllFlashData(): void
    {
        $this->flashBag->flash($this->session, 'error', 'Boom');

        $this->flashBag->clear($this->session);

        self::assertFalse($this->flashBag->has($this->session, 'error'));
    }
}
