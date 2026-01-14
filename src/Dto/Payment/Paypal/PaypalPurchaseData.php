<?php declare(strict_types=1);

namespace Asterios\Core\Dto\Payment\Paypal;

use Asterios\Core\Data;

final class PaypalPurchaseData extends Data
{
    public function __construct(
        public readonly string $merchantOrderId,
        public readonly float  $amount,
        public readonly string $currency,

        public readonly string $returnUrl,
        public readonly string $cancelUrl,
        public readonly string $captureUrl,

        public readonly string $description = '',
        public readonly array  $customData = []
    ) {}
}
