<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */

namespace Amasty\Stripe\Model\Quote;

use Amasty\Stripe\Api\Quote\ApplePayGuestShippingMethodManagementInterface;
use Amasty\Stripe\Api\Quote\ApplePayShippingMethodManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\GuestCart\GuestShippingMethodManagement as GuestShippingMethodManagementOriginal;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Shipping method read service
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestShippingMethodManagement extends GuestShippingMethodManagementOriginal implements
    ApplePayGuestShippingMethodManagementInterface
{

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var ShippingMethodManagementInterface
     */
    private $shippingMethodManagement;

    public function __construct(
        ApplePayShippingMethodManagementInterface $shippingMethodManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        parent::__construct($shippingMethodManagement, $quoteIdMaskFactory);
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @inheritdoc
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->shippingMethodManagement->estimateByExtendedAddress($quoteIdMask->getQuoteId(), $address);
    }

    /**
     * {@inheritDoc}
     */
    public function set($cartId, $carrierCode, $methodCode, AddressInterface $address = null)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->shippingMethodManagement->set($quoteIdMask->getQuoteId(), $carrierCode, $methodCode, $address);
    }
}
