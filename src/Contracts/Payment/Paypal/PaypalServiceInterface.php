<?php declare(strict_types=1);

namespace Asterios\Core\Contracts\Payment\Paypal;

use Asterios\Core\Dto\Payment\Paypal\PaypalPurchaseData;
use Asterios\Core\Exception\PaypalException;

interface PaypalServiceInterface
{
    /**
     * @param PaypalPurchaseData $purchase
     * @return array
     * @throws PaypalException
     */
    public function createOrder(PaypalPurchaseData $purchase): array;

    /**
     * @param string $paypalOrderId
     * @return array
     * @throws PaypalException
     */
    public function capture(string $paypalOrderId): array;
}
