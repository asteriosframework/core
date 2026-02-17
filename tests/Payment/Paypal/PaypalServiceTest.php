<?php declare(strict_types=1);

namespace Asterios\Test\Payment\Paypal;

use Asterios\Core\Contracts\Payment\Paypal\PaypalOAuthTokenProviderInterface;
use Asterios\Core\Dto\Payment\Paypal\PaypalConfigData;
use Asterios\Core\Dto\Payment\Paypal\PaypalPurchaseData;
use Asterios\Core\Exception\PaypalException;
use Asterios\Core\Payment\Paypal\PaypalService;
use Asterios\Core\Request;
use JsonException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;

class PaypalServiceTest extends MockeryTestCase
{
    private PaypalConfigData $config;
    private PaypalOAuthTokenProviderInterface|m\MockInterface $tokenProvider;
    private Request|m\MockInterface $request;
    private PaypalService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new PaypalConfigData(
            'fake-client-id',
            'fake-secret',
            'https://api.sandbox.paypal.com'
        );

        $this->tokenProvider = m::mock(PaypalOAuthTokenProviderInterface::class);
        $this->request = m::mock(Request::class);

        $this->service = new PaypalService(
            $this->config,
            $this->tokenProvider,
            $this->request
        );
    }

    /**
     * @throws PaypalException
     * @throws JsonException
     */
    public function testCreateOrderSuccess(): void
    {
        $purchase = $this->createPurchaseData();

        $this->tokenProvider->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('fake-token');

        $response = new stdClass();
        $response->body = json_encode([
            'id' => 'PAYPAL-ID-999',
            'links' => [
                ['rel' => 'approve', 'href' => 'https://paypal.com/approve/123']
            ]
        ], JSON_THROW_ON_ERROR);

        $this->request->shouldReceive('post')
            ->once()
            ->with(
                $this->config->baseUrl . '/v2/checkout/orders',
                m::type('string')
            )
            ->andReturn($response);

        $result = $this->service->createOrder($purchase);

        $this->assertEquals('PAYPAL-ID-999', $result['paypalOrderId']);
        $this->assertEquals('https://paypal.com/approve/123', $result['approveUrl']);
        $this->assertEquals('Bearer fake-token', $this->request->headers['Authorization']);
    }

    public function testCreateOrderThrowsExceptionOnMissingResponse(): void
    {
        $purchase = $this->createPurchaseData();
        $this->tokenProvider->shouldReceive('getAccessToken')->andReturn('token');

        $this->request->shouldReceive('post')->andReturn(null);

        $this->expectException(PaypalException::class);
        $this->expectExceptionMessage('PayPal createOrder failed');

        $this->service->createOrder($purchase);
    }

    /**
     * @throws PaypalException
     * @throws JsonException
     */
    public function testCreateOrderThrowsExceptionIfApproveUrlMissing(): void
    {
        $purchase = $this->createPurchaseData();
        $this->tokenProvider->shouldReceive('getAccessToken')->andReturn('token');

        $response = new stdClass();
        $response->body = json_encode([
            'id' => '123',
            'links' => [['rel' => 'self', 'href' => '...']]
        ], JSON_THROW_ON_ERROR);

        $this->request->shouldReceive('post')->andReturn($response);

        $this->expectException(PaypalException::class);
        $this->expectExceptionMessage('Approve URL missing');

        $this->service->createOrder($purchase);
    }

    /**
     * @throws PaypalException
     * @throws JsonException
     */
    public function testCaptureSuccess(): void
    {
        $paypalOrderId = 'PAYPAL-123';

        $this->tokenProvider->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('token-456');

        $response = new stdClass();
        $response->body = json_encode(['status' => 'COMPLETED'], JSON_THROW_ON_ERROR);

        $expectedUrl = $this->config->baseUrl . '/v2/checkout/orders/' . $paypalOrderId . '/capture';

        $this->request->shouldReceive('post')
            ->once()
            ->with($expectedUrl)
            ->andReturn($response);

        $result = $this->service->capture($paypalOrderId);

        $this->assertEquals('COMPLETED', $result['status']);
        $this->assertEquals('Bearer token-456', $this->request->headers['Authorization']);
    }

    public function testCaptureThrowsExceptionOnFailedRequest(): void
    {
        $this->tokenProvider->shouldReceive('getAccessToken')->andReturn('token');
        $this->request->shouldReceive('post')->andReturn(null);

        $this->expectException(PaypalException::class);
        $this->expectExceptionMessage('PayPal capture failed');

        $this->service->capture('123');
    }

    public function testCaptureThrowsExceptionOnInvalidJsonResponse(): void
    {
        $this->tokenProvider->shouldReceive('getAccessToken')->andReturn('token');

        $response = new stdClass();
        $response->body = '{ invalid json ]';

        $this->request->shouldReceive('post')->andReturn($response);

        $this->expectException(PaypalException::class);

        $this->service->capture('123');
    }

    /**
     * @return PaypalPurchaseData
     */
    private function createPurchaseData(): PaypalPurchaseData
    {
        return new PaypalPurchaseData(
            'ORDER-123',
            100.00,
            'EUR',
            'https://example.com/return',
            'https://example.com/cancel',
            'https://example.com/capture'
        );
    }
}
