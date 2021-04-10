<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Model\Ui;

use Amasty\Stripe\Gateway\Config\Config;
use Amasty\Stripe\Model\StripeAccountManagement;
use Amasty\Stripe\Model\Validator\StripeEnabledValidator;
use Magento\Checkout\Model\ConfigProviderInterface;
use Amasty\Stripe\Gateway\Helper\AmountHelper;

/**
 * Provide Data For Components
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'amasty_stripe';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var StripeAccountManagement
     */
    private $stripeAccountManagement;

    /**
     * @var StripeEnabledValidator
     */
    private $stripeEnabledValidator;

    public function __construct(
        Config $config,
        StripeAccountManagement $stripeAccountManagement,
        StripeEnabledValidator $stripeEnabledValidator
    ) {
        $this->config = $config;
        $this->stripeAccountManagement = $stripeAccountManagement;
        $this->stripeEnabledValidator = $stripeEnabledValidator;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        if (!$this->stripeEnabledValidator->validate()) {
            return [];
        }

        return [
            'payment' => [
                static::CODE => [
                    'isActive' => $this->config->isActive(),
                    'isApplePayEnabled' => $this->config->isApplePayEnabled(),
                    'publicKey' => $this->config->getPublicKey(),
                    'sdkUrl' => $this->config->getSdkUrl(),
                    'threedSecureAlways' => $this->config->getThreedSecureAlways(),
                    'imageUrl' => $this->config->getImageUrl(),
                    'savedCards' => $this->stripeAccountManagement->getAllCards(),
                    'enableSaveCards' => $this->config->isEnableSaveCards(),
                    'zeroDecimal' => AmountHelper::CURRENCY_ZERO_DECIMAL
                ],
            ]
        ];
    }
}
