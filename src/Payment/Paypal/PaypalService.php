<?php declare(strict_types=1);

namespace Asterios\Core\Payment\Paypal;

use Asterios\Core\Contracts\Payment\Paypal\PaypalOAuthTokenProviderInterface;
use Asterios\Core\Contracts\Payment\Paypal\PaypalServiceInterface;
use Asterios\Core\Dto\Payment\Paypal\PaypalConfigData;
use Asterios\Core\Dto\Payment\Paypal\PaypalPurchaseData;
use Asterios\Core\Exception\PaypalException;
use Asterios\Core\Request;
use JsonException;

final readonly class PaypalService implements PaypalServiceInterface
{
    public function __construct(
        private PaypalConfigData $config,
        private PaypalOAuthTokenProviderInterface $tokenProvider,
        private Request $request
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createOrder(PaypalPurchaseData $purchase): array
    {
        $accessToken = $this->tokenProvider->getAccessToken();

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $purchase->merchantOrderId,
                'description'  => $purchase->description,
                'amount' => [
                    'currency_code' => $purchase->currency,
                    'value' => number_format($purchase->amount, 2, '.', '')
                ]
            ]],
            'application_context' => [
                'return_url' => $purchase->captureUrl,
                'cancel_url' => $purchase->cancelUrl,
                'user_action' => 'PAY_NOW'
            ]
        ];

        $this->request->headers = [
            'Authorization' => 'Bearer '.$accessToken,
            'Content-Type'  => 'application/json'
        ];

        try
        {
            $response = $this->request->post(
                $this->config->baseUrl . '/v2/checkout/orders',
                json_encode($payload, JSON_THROW_ON_ERROR)
            );
        } // @codeCoverageIgnoreStart
        catch (JsonException $e)
        {
            throw new PaypalException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd

        if (!$response)
        {
            throw new PaypalException('PayPal createOrder failed');
        }

        try
        {
            $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        }  // @codeCoverageIgnoreStart
        catch (JsonException $e)
        {
            throw new PaypalException($e->getMessage(), $e->getCode(), $e);
        } // @codeCoverageIgnoreEnd

        return [
            'paypalOrderId' => $data['id'],
            'approveUrl'    => $this->extractApproveUrl($data)
        ];
    }

    /**
     * @inheritDoc
     */
    public function capture(string $paypalOrderId): array
    {
        $accessToken = $this->tokenProvider->getAccessToken();

        $this->request->headers = [
            'Authorization' => 'Bearer '.$accessToken,
            'Content-Type'  => 'application/json'
        ];

        $response = $this->request->post($this->config->baseUrl.'/v2/checkout/orders/'.$paypalOrderId.'/capture');

        if (!$response)
        {
            throw new PaypalException('PayPal capture failed');
        }

        try
        {
            return json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        }
        catch (JsonException $e)
        {
            throw new PaypalException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param array $response
     * @return string
     * @throws PaypalException
     */
    private function extractApproveUrl(array $response): string
    {
        foreach ($response['links'] as $link)
        {
            if ($link['rel'] === 'approve')
            {
                return $link['href'];
            }
        }

        throw new PaypalException('Approve URL missing');
    }
}