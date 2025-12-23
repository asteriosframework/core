<?php declare(strict_types=1);

namespace Asterios\Test;

use Asterios\Core\GoogleAuthenticator;
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
}
