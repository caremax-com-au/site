<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Block\Customer\Element\Html\Link;

use Amasty\Stripe\Model\Validator\StripeEnabledValidator;

class Current extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Amasty\Stripe\Model\StripeAccountManagement
     */
    private $stripeAccountManagement;

    /**
     * @var \Amasty\Stripe\Gateway\Config\Config
     */
    private $config;

    /**
     * @var StripeEnabledValidator
     */
    private $stripeEnabledValidator;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Amasty\Stripe\Model\StripeAccountManagement $stripeAccountManagement,
        \Amasty\Stripe\Gateway\Config\Config $config,
        StripeEnabledValidator $stripeEnabledValidator,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->stripeAccountManagement = $stripeAccountManagement;
        $this->config = $config;
        $this->stripeEnabledValidator = $stripeEnabledValidator;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->stripeEnabledValidator->validate()) {
            return '';
        }

        if (!$this->config->isEnableSaveCards() && !$this->stripeAccountManagement->getAllCards()) {
            return '';
        }

        return parent::_toHtml();
    }
}
