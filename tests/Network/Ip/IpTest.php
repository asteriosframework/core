<?php declare(strict_types=1);

namespace Asterios\Test\Network\Ip;

use Asterios\Core\Enum\Network\Ip\IpVersion;
use Asterios\Core\Exception\Network\Ip\InvalidIpException;
use Asterios\Core\Exception\Network\Ip\InvalidIpRangeException;
use Asterios\Core\Network\Ip\Ip;
use PHPUnit\Framework\TestCase;

final class IpTest extends TestCase
{
    private Ip $ip;

    protected function setUp(): void
    {
        $this->ip = new Ip();
    }

    /**
     * @return void
     */
    public function testValidIpv4IsRecognized(): void
    {
        self::assertTrue(
            $this->ip->isValid('192.168.1.1')
        );
    }

    /**
     * @return void
     */
    public function testValidIpv6IsRecognized(): void
    {
        self::assertTrue(
            $this->ip->isValid('2001:db8::1')
        );
    }

    /**
     * @return void
     */
    public function testInvalidIpIsRejected(): void
    {
        self::assertFalse(
            $this->ip->isValid('not-an-ip')
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     */
    public function testDetectsIpv4Version(): void
    {
        self::assertSame(
            IpVersion::IPv4,
            $this->ip->version('192.168.1.1')
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     */
    public function testDetectsIpv6Version(): void
    {
        self::assertSame(
            IpVersion::IPv6,
            $this->ip->version('2001:db8::1')
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     */
    public function testInvalidIpThrowsExceptionOnVersionDetection(): void
    {
        $this->expectException(InvalidIpException::class);

        $this->ip->version('invalid-ip');
    }

    /**
     * @return void
     * @throws InvalidIpException
     * @throws InvalidIpRangeException
     */
    public function testIpv4MatchesCidrRange(): void
    {
        self::assertTrue(
            $this->ip->inRange('192.168.1.50', '192.168.1.0/24')
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     * @throws InvalidIpRangeException
     */
    public function testIpv4RejectsOutsideCidrRange(): void
    {
        self::assertFalse(
            $this->ip->inRange('192.168.2.50', '192.168.1.0/24')
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     * @throws InvalidIpRangeException
     */
    public function testIpv4MatchesWildcardRange(): void
    {
        self::assertTrue(
            $this->ip->inRange('10.0.5.20', '10.0.*.*')
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     * @throws InvalidIpRangeException
     */
    public function testIpv4MatchesExplicitRange(): void
    {
        self::assertTrue(
            $this->ip->inRange(
                '172.16.0.10',
                '172.16.0.1-172.16.0.20'
            )
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     * @throws InvalidIpRangeException
     */
    public function testIpv4RejectsOutsideExplicitRange(): void
    {
        self::assertFalse(
            $this->ip->inRange(
                '172.16.0.50',
                '172.16.0.1-172.16.0.20'
            )
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     * @throws InvalidIpRangeException
     */
    public function testInvalidIpv4RangeThrowsException(): void
    {
        $this->expectException(InvalidIpRangeException::class);

        $this->ip->inRange('192.168.1.1', 'invalid-range');
    }

    /**
     * @return void
     * @throws InvalidIpException
     * @throws InvalidIpRangeException
     */
    public function testIpv6MatchesCidrRange(): void
    {
        self::assertTrue(
            $this->ip->inRange(
                '2001:db8::1',
                '2001:db8::/64'
            )
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     * @throws InvalidIpRangeException
     */
    public function testIpv6RejectsOutsideCidrRange(): void
    {
        self::assertFalse(
            $this->ip->inRange(
                '2001:dead::1',
                '2001:db8::/64'
            )
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     * @throws InvalidIpRangeException
     */
    public function testInvalidIpv6RangeThrowsException(): void
    {
        $this->expectException(InvalidIpRangeException::class);

        $this->ip->inRange(
            '2001:db8::1',
            'invalid-ipv6-range'
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     */
    public function testNormalizeIpv4ReturnsOriginalValue(): void
    {
        self::assertSame(
            '127.0.0.1',
            $this->ip->normalize('127.0.0.1')
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     */
    public function testNormalizeIpv6ReturnsNormalizedBinaryHex(): void
    {
        self::assertSame(
            '20010db8000000000000000000000001',
            $this->ip->normalize('2001:db8::1')
        );
    }

    /**
     * @return void
     * @throws InvalidIpException
     */
    public function testNormalizeInvalidIpThrowsException(): void
    {
        $this->expectException(InvalidIpException::class);

        $this->ip->normalize('totally-invalid');
    }
}