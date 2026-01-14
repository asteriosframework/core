<?php declare(strict_types=1);

namespace Asterios\Test\Payment\Paypal;

use Asterios\Core\Dto\Payment\Paypal\PaypalConfigData;
use Asterios\Core\Exception\PaypalException;
use Asterios\Core\Payment\Paypal\PaypalOAuthTokenProvider;
use Asterios\Core\Request;
use JsonException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;

class PaypalOAuthTokenProviderTest extends MockeryTestCase
{
    private PaypalConfigData $config;
    private Request|m\MockInterface $request;
    private PaypalOAuthTokenProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new PaypalConfigData(
            'client-id-123',
            'secret-456',
            'https://api.sandbox.paypal.com'
        );

        $this->request = m::mock(Request::class);

        $this->provider = new PaypalOAuthTokenProvider(
            $this->config,
            $this->request
        );
    }

    /**
     * @throws PaypalException
     * @throws JsonException
     */
    public function testGetAccessTokenSuccess(): void
    {
        $expectedAuth = 'Basic ' . base64_encode('client-id-123:secret-456');

        $response = new stdClass();
        $response->body = json_encode([
            'access_token' => 'mocked-access-token-789',
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ], JSON_THROW_ON_ERROR);

        $this->request->shouldReceive('post')
            ->once()
            ->with(
                $this->config->baseUrl . '/v1/oauth2/token',
                'grant_type=client_credentials'
            )
            ->andReturn($response);

        $token = $this->provider->getAccessToken();

        $this->assertEquals('mocked-access-token-789', $token);
        $this->assertEquals($expectedAuth, $this->request->headers['Authorization']);
        $this->assertEquals('application/x-www-form-urlencoded', $this->request->headers['Content-Type']);
    }

    public function testGetAccessTokenThrowsExceptionOnHttpError(): void
    {
        $this->request->shouldReceive('post')->andReturn(null);

        $this->expectException(PaypalException::class);
        $this->expectExceptionMessage('PayPal OAuth HTTP error');

        $this->provider->getAccessToken();
    }

    public function testGetAccessTokenThrowsExceptionOnInvalidJson(): void
    {
        $response = new stdClass();
        $response->body = '{ "invalid": json ... }';

        $this->request->shouldReceive('post')->andReturn($response);

        $this->expectException(PaypalException::class);

        $this->provider->getAccessToken();
    }

    /**
     * @throws JsonException
     */
    public function testGetAccessTokenThrowsExceptionIfTokenMissingInResponse(): void
    {
        $response = new stdClass();
        $response->body = json_encode(['error' => 'invalid_client'], JSON_THROW_ON_ERROR);

        $this->request->shouldReceive('post')->andReturn($response);

        $this->expectException(PaypalException::class);
        $this->expectExceptionMessage('PayPal OAuth failed');

        $this->provider->getAccessToken();
    }
}