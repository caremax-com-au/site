<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Block;

use Magento\Payment\Block\Form\Cc;

class Form extends Cc
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Stripe::order/stripe.phtml';

    /**
     * @var \Amasty\Stripe\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Amasty\Stripe\Model\StripeAccountManagement
     */
    private $stripeAccountManagement;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Amasty\Stripe\Gateway\Config\Config $config,
        \Amasty\Stripe\Model\StripeAccountManagement $stripeAccountManagement,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->config = $config;
        $this->stripeAccountManagement = $stripeAccountManagement;
    }

    /**
     * @inheritDoc
     */
    public function getJsLayout()
    {
        $this->jsLayout['components'] = null;
        $cardsData = $this->stripeAccountManagement->getAllCards();

        $result = [
            'component' => 'Amasty_Stripe/js/view/customer/order/stripe-form',
            'sdkUrl' => $this->config->getSdkUrl(),
            'publicKey' => $this->config->getPublicKey(),
            'cardsData' => $cardsData,
            'threedSecureAlways' => $this->config->getThreedSecureAlways(),
            'currency' => $this->getCurrentCurrency(),
            'secretUrl' => $this->config->getSecretUrl()
        ];

        $this->jsLayout['components']['amasty-stripe-saved-cards'] = $result;

        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * @return string
     */
    private function getCurrentCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }
}
