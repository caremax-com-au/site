<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */

namespace Amasty\Stripe\Model\Quote;

use Amasty\Stripe\Api\Quote\ApplePayShippingMethodManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\ShippingMethodManagement as ShippingMethodManagementOriginal;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class ShippingMethodManagement extends ShippingMethodManagementOriginal implements
    ApplePayShippingMethodManagementInterface
{

    /**
     * @var Totals
     */
    private $totals;

    /**
     * @var AddressMerger
     */
    private $addressMerger;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ShippingMethodConverter $converter,
        AddressRepositoryInterface $addressRepository,
        TotalsCollector $totalsCollector,
        Totals $totals,
        AddressMerger $addressMerger,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($quoteRepository, $converter, $addressRepository, $totalsCollector);
        $this->totals = $totals;
        $this->addressMerger = $addressMerger;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function set($cartId, $carrierCode, $methodCode, AddressInterface $address = null, $calculateTotals = true)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $this->addressMerger->merge($quote, $address);

        try {
            $this->apply($cartId, $carrierCode, $methodCode);
        } catch (\Exception $e) {
            throw $e;
        }

        if ($calculateTotals) {
            return $this->totals->getTotals($quote);
        }

        return [];
    }

    /**
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     *
     * @return array|\Magento\Quote\Api\Data\ShippingMethodInterface[]
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        $estimates = parent::estimateByExtendedAddress($cartId, $address);

        if ($estimates) {
            $first = $estimates[0];
            $this->set(
                $cartId,
                $first->getCarrierCode(),
                $first->getMethodCode()
            );
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $estimatesArray = array_map(function ($item) {
            return [
                'carrier_code'  => $item->getCarrierCode(),
                'method_code'   => $item->getMethodCode(),
                'method_title'  => $item->getMethodTitle(),
                'carrier_title' => $item->getCarrierTitle(),
                'amount'        => $this->priceCurrency->round($item->getAmount()),
            ];
        }, $estimates);

        return [$estimatesArray, $this->totals->getTotals($quote)];
    }
}
