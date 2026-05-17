<?php declare(strict_types=1);

namespace Asterios\Test\Session\Store;

use Asterios\Core\Session\Store\ExpiringStore;
use PHPUnit\Framework\TestCase;

final class ExpiringStoreTest extends TestCase
{
    private ExpiringStore $store;

    /**
     * @var array<string, mixed>
     */
    private array $session;

    protected function setUp(): void
    {
        $this->store = new ExpiringStore();
        $this->session = [];
    }

    public function testPutStoresValueWithTtl(): void
    {
        $this->store->put($this->session, 'otp.code', '123456', 60);

        self::assertSame('123456', $this->session['otp']['code']);
        self::assertFalse($this->store->hasExpired($this->session, 'otp.code'));
    }

    public function testHasExpiredReturnsFalseForUnknownKey(): void
    {
        self::assertFalse($this->store->hasExpired($this->session, 'missing.key'));
    }

    public function testPurgeRemovesExpiredEntries(): void
    {
        $this->store->put($this->session, 'token', 'abc', 0);

        $this->store->purge($this->session);

        self::assertArrayNotHasKey('token', $this->session);
    }

    public function testClearRemovesAllTtlMetadata(): void
    {
        $this->store->put($this->session, 'foo', 'bar', 100);

        $this->store->clear($this->session);

        self::assertSame([], $this->session['__asterios']['ttl']);
    }
}
