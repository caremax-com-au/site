<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Model;

use Amasty\Stripe\Gateway\Helper\AmountHelper;
use Amasty\Stripe\Model\Adapter\StripeAdapter;
use Amasty\Stripe\Gateway\Config\Config as StripeConfig;
use Amasty\Stripe\Model\StripeAccountManagement;
use Stripe\PaymentIntent;

/**
 * Adapter for Payment Intent Stripe
 */
class PaymentIntentRegistry
{
    /**
     * @var AmountHelper
     */
    private $amountHelper;

    /**
     * @var StripeAdapter
     */
    private $stripeAdapter;

    /**
     * @var StripeConfig
     */
    private $stripeConfig;

    /**
     * @var StripeAccountManagement
     */
    private $accountManager;

    public function __construct(
        AmountHelper $amountHelper,
        StripeAdapter $stripeAdapter,
        StripeConfig $stripeConfig,
        StripeAccountManagement $accountManager
    ) {
        $this->amountHelper = $amountHelper;
        $this->stripeAdapter = $stripeAdapter;
        $this->stripeConfig = $stripeConfig;
        $this->accountManager = $accountManager;
    }

    /**
     * @param float $grandTotal
     * @param string $currency
     *
     * @return array
     */
    public function getPaymentIntentsDataSecret($grandTotal, $currency)
    {
        $grandTotal = $this->amountHelper->getAmountForStripe($grandTotal, $currency);
        $this->stripeAdapter->initCredentials();
        $authorizeMethod = $this->stripeConfig->getAuthorizeMethod();
        $customer = $this->accountManager->getCurrentStripeCustomerId();
        $intent = PaymentIntent::create(
            [
                'amount' => $grandTotal,
                'currency' => $currency,
                'payment_method_types' => ["card"],
                'capture_method' => $authorizeMethod == 'authorize' ? 'manual' : 'automatic',
                'customer' => $customer
            ]
        );

        return ['pi' => $intent->id, 'secret' => $intent->client_secret];
    }
}
