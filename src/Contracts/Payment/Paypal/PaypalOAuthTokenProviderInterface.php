<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Payment\Paypal;

use Asterios\Core\Exception\PaypalException;

interface PaypalOAuthTokenProviderInterface {

    /**
     * @return string
     * @throws PaypalException
     */
    public function getAccessToken(): string;
}