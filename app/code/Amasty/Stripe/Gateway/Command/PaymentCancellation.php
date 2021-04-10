<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


declare(strict_types=1);

namespace Amasty\Stripe\Gateway\Command;

use Amasty\Stripe\Model\Adapter\StripeAdapter;
use Amasty\Stripe\Model\Ui\ConfigProvider;
use Magento\Quote\Api\Data\PaymentInterface;

class PaymentCancellation
{
    /**
     * @var StripeAdapter
     */
    private $stripeAdapter;

    public function __construct(StripeAdapter $stripeAdapter)
    {
        $this->stripeAdapter = $stripeAdapter;
    }

    /**
     * @param PaymentInterface $paymentMethod
     */
    public function execute(PaymentInterface $paymentMethod): void
    {
        if ($paymentMethod->getMethod() === ConfigProvider::CODE
            && $paymentIntentId = $paymentMethod->getAdditionalData()['source']
        ) {
            if ($intent = $this->stripeAdapter->paymentIntentRetrieve($paymentIntentId)) {
                if ($intent->capture_method === "manual") {
                    $this->stripeAdapter->paymentIntentCancel($intent);
                } else {
                    foreach ($intent->charges->data as $charge) {
                        $this->stripeAdapter->refundCreate(['charge' => $charge->id]);
                    }
                }
            }
        }
    }
}
