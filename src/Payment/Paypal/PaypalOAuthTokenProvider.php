<?php declare(strict_types=1);

namespace Asterios\Core\Payment\Paypal;

use Asterios\Core\Contracts\Payment\Paypal\PaypalOAuthTokenProviderInterface;
use Asterios\Core\Dto\Payment\Paypal\PaypalConfigData;
use Asterios\Core\Exception\PaypalException;
use Asterios\Core\Request;

readonly class PaypalOAuthTokenProvider implements PaypalOAuthTokenProviderInterface
{
    public function __construct(
        private PaypalConfigData $config,
        private Request $request
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAccessToken(): string
    {
        $this->request->headers = [
            'Authorization' => 'Basic ' . base64_encode(
                $this->config->clientId . ':' . $this->config->secret
            ),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        $response = $this->request->post(
            $this->config->baseUrl . '/v1/oauth2/token',
            'grant_type=client_credentials'
        );

        if (!$response)
        {
            throw new PaypalException('PayPal OAuth HTTP error');
        }

        try
        {
            $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        }
        catch (\JsonException $e)
        {
            throw new PaypalException($e->getMessage(), $e->getCode(), $e);
        }

        if (!isset($data['access_token']))
        {
            throw new PaypalException('PayPal OAuth failed');
        }

        return $data['access_token'];
    }
}