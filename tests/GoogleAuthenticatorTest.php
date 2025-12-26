<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\GoogleAuthenticator\GoogleAuthenticator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OTPHP\TOTPInterface;

class GoogleAuthenticatorTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testVerifyReturnsTrue(): void
    {
        $mockTOTP = m::mock(TOTPInterface::class);
        $mockTOTP->shouldReceive('verify')
            ->once()
            ->with('123456', null, 1)
            ->andReturn(true);

        $authenticator = new GoogleAuthenticator($mockTOTP);

        $this->assertTrue($authenticator->verify('123456'));
    }

    public function testVerifyWithTimestamp(): void
    {
        $timestamp = time();

        $mockTOTP = m::mock(TOTPInterface::class);
        $mockTOTP->shouldReceive('verify')
            ->once()
            ->with('654321', $timestamp, 2)
            ->andReturn(true);

        $authenticator = new GoogleAuthenticator($mockTOTP);

        $this->assertTrue($authenticator->verify('654321', 2, $timestamp));
    }

    public function testGetSecretAndProvisioningUri(): void
    {
        $mockTOTP = m::mock(TOTPInterface::class);
        $mockTOTP->shouldReceive('getSecret')->once()->andReturn('SECRET123');
        $mockTOTP->shouldReceive('getProvisioningUri')->once()->andReturn('otpauth://totp/...');

        $authenticator = new GoogleAuthenticator($mockTOTP);

        $this->assertSame('SECRET123', $authenticator->getSecret());
        $this->assertSame('otpauth://totp/...', $authenticator->getProvisioningUri());
    }

    public function testGenerateBase32SecretReturnsValidString(): void
    {
        $mockTOTP = m::mock(TOTPInterface::class);
        $authenticator = new GoogleAuthenticator($mockTOTP);

        $length = 16;
        $secret = $authenticator->generateBase32Secret($length);

        // Base32 Alphabet Check
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);

        // Die Länge des resultierenden Strings bei Base32 ist (bytes * 8) / 5, aufgerundet.
        // Für 16 Bytes: (16 * 8) / 5 = 128 / 5 = 25.6 -> 26 Zeichen.
        $expectedLength = (int) ceil(($length * 8) / 5);
        $this->assertEquals($expectedLength, strlen($secret));
    }

    public function testGenerateBase32SecretWithDifferentLength(): void
    {
        $mockTOTP = m::mock(TOTPInterface::class);
        $authenticator = new GoogleAuthenticator($mockTOTP);

        $length = 10;
        $secret = $authenticator->generateBase32Secret($length);

        $expectedLength = (int) ceil(($length * 8) / 5);
        $this->assertEquals($expectedLength, strlen($secret));
    }
}
