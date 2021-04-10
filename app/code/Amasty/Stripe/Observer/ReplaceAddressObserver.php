<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */

namespace Amasty\Stripe\Observer;

use Amasty\Stripe\Api\Quote\ApplePayShippingMethodManagementInterface;
use Amasty\Stripe\Model\Quote\AddressMerger;
use Amasty\Stripe\Model\Quote\ShippingMethodManagement;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\Quote\TotalsCollector;
use Zend\Filter\Word\UnderscoreToCamelCase;

class ReplaceAddressObserver implements ObserverInterface
{

    /**
     * @var AddressInterface
     */
    private $address;

    /**
     * @var UnderscoreToCamelCase
     */
    private $camelCase;

    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var ShippingMethodManagement
     */
    private $shippingMethodManagement;

    /**
     * @var ShipmentEstimationInterface
     */
    private $shipmentEstimation;

    /**
     * @var AddressMerger
     */
    private $addressMerger;

    public function __construct(
        AddressInterface $address,
        UnderscoreToCamelCase $camelCase,
        TotalsCollector $totalsCollector,
        ApplePayShippingMethodManagementInterface $shippingMethodManagement,
        ShipmentEstimationInterface $shipmentEstimation,
        AddressMerger $addressMerger
    ) {
        $this->address = $address;
        $this->camelCase = $camelCase;
        $this->totalsCollector = $totalsCollector;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->shipmentEstimation = $shipmentEstimation;
        $this->addressMerger = $addressMerger;
    }

    /**
     * @param Observer $observer
     *
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Payment $payment */
        $payment = $observer->getEvent()->getPayment();
        /** @var DataObject $input */
        $input = $observer->getEvent()->getInput();
        $additionalData = $input->getData('additional_data');

        if (empty($additionalData['apple_pay'])) {
            return;
        }

        $applePayData = json_decode($additionalData['apple_pay']);

        unset($additionalData['apple_pay']);
        $input->setAdditionalData($additionalData);
        if (!empty($applePayData->selectedAddress) && !empty($applePayData->selectedShippingMethod)) {
            $method = explode('|', $applePayData->selectedShippingMethod);
            if (count($method) !== 2) {
                return;
            }

            $quote = $payment->getQuote();
            $quote->getShippingAddress()
                ->setSameAsBilling(false);

            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setSameAsBilling(false)
                ->setCustomerAddressId(null);

            $this->buildAddressFromData((array)$applePayData->selectedAddress);

            $shippingAddress->setLimitCarrier(null);
            $this->addressMerger->merge($quote, $this->address);

            $this->shipmentEstimation->estimateByExtendedAddress(
                $quote->getEntityId(),
                $shippingAddress
            );
            $this->shippingMethodManagement->set(
                $quote->getEntityId(),
                $method[0],
                $method[1],
                $shippingAddress,
                false
            );
        }
    }

    /**
     * @param array $data
     */
    protected function buildAddressFromData($data)
    {
        foreach ($data as $key => $value) {
            $key = $this->camelCase->filter($key);
            $this->address->{'set' . ucfirst($key)}($value);
        }
    }
}
