<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */

namespace Amasty\Stripe\Api\Quote;

use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Interface ApplePayShippingMethodManagementInterface
 *
 * @api
 */
interface ApplePayShippingMethodManagementInterface extends ShippingMethodManagementInterface
{

    /**
     * Sets the carrier and shipping methods codes for a specified cart.
     *
     * @param mixed $cartId The shopping cart ID.
     * @param string $carrierCode The carrier code.
     * @param string $methodCode The shipping method code.
     * @param AddressInterface|null $address
     *
     * @return bool
     * @throws \Magento\Framework\Exception\InputException The shipping method is not valid for an empty cart.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The shipping method could not be saved.
     * @throws \Magento\Framework\Exception\StateException The billing or shipping address is not set.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart contains only virtual products
     * so the shipping method does not apply.
     */
    public function set($cartId, $carrierCode, $methodCode, AddressInterface $address = null);

    /**
     * Estimate shipping by address and return list of available shipping methods
     *
     * @param mixed $cartId
     * @param AddressInterface $address
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address);
}
