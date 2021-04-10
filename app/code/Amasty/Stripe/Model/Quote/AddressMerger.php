<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */

namespace Amasty\Stripe\Model\Quote;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;

class AddressMerger
{

    /**
     * @var CollectionFactory
     */
    private $regionCollectionFactory;

    public function __construct(CollectionFactory $regionCollectionFactory)
    {
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * @param Quote $quote
     * @param AddressInterface|null $address
     */
    public function merge(Quote $quote, AddressInterface $address = null)
    {
        if ($address) {
            $regionId = $this->getRegionId($address->getRegion(), $address->getCountryId());
            $quote->getShippingAddress()
                ->addData(array_filter($address->getData()))
                ->setCollectShippingRates(true)
                ->setRegionId($regionId)
                ->setCustomerAddressId(null)
                ->setSameAsBilling(false);
        }
    }

    /**
     * Return region Id by code or name.
     *
     * @param string $region
     * @param string $countryId
     *
     * @return string|null
     */
    private function getRegionId($region, $countryId)
    {
        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addCountryFilter($countryId)
            ->addRegionCodeOrNameFilter($region)
            ->setPageSize(1);

        return $regionCollection->getFirstItem()->getId();
    }
}
