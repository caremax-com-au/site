<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Block\Customer;

class SavedCards extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Stripe\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Amasty\Stripe\Model\StripeAccountManagement
     */
    private $stripeAccountManagement;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Stripe\Gateway\Config\Config $config,
        \Amasty\Stripe\Model\StripeAccountManagement $stripeAccountManagement,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->stripeAccountManagement = $stripeAccountManagement;
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    public function getJsLayout()
    {
        $this->jsLayout['components'] = null;
        $cardsData = $this->stripeAccountManagement->getAllCards();

        $result = [
            'component' => 'Amasty_Stripe/js/view/customer/saved-cards',
            'sdkUrl' => $this->config->getSdkUrl(),
            'publicKey' => $this->config->getPublicKey(),
            'cardsData' => $cardsData,
            'deleteCardUrl' => $this->_urlBuilder->getUrl('amstripe/customer/deleteCard'),
            'addCardUrl' => $this->_urlBuilder->getUrl('amstripe/customer/addCard'),
            'billingAddress' => $this->getBillingAddress(),
            'enableSaveCards' => $this->config->isEnableSaveCards()
        ];

        $this->jsLayout['components']['amasty-stripe-saved-cards'] = $result;

        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * @return array|bool
     */
    private function getBillingAddress()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->session->getCustomer();
        $defBillingAddress = $customer->getDefaultBillingAddress();

        if ($defBillingAddress && $billingAddressData = $defBillingAddress->getData()) {
            $street = explode(PHP_EOL, $billingAddressData['street']);
            $billingAddress = [
                'billing_details' => [
                    'name' => $billingAddressData['firstname'] . ' ' . $billingAddressData['lastname'],
                    'address' => [
                        'postal_code' => isset($billingAddressData['postcode']) ? $billingAddressData['postcode'] : '',
                        'country' => isset($billingAddressData['country_id']) ? $billingAddressData['country_id'] : '',
                        'city' => isset($billingAddressData['city']) ? $billingAddressData['city'] : '',
                        'state' => isset($billingAddressData['region']) ? $billingAddressData['region'] : '',
                        'line1' => isset($street['0']) ? $street['0'] : '',
                        'line2' => isset($street['1']) ? $street['1'] : ''
                    ]
                ]
            ];

            if (!empty($billingAddressData['telephone'])) {
                $billingAddress['billing_details'] =
                    array_merge($billingAddress['billing_details'], ['phone' => $billingAddressData['telephone']]);
            }
        } else {
            $billingAddress = false;
        }

        return $billingAddress;
    }
}
