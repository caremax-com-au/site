<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\ResourceModel\Method\Collection as MethodCollection;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Rate Data of Shipping Method.
 *  Shipping Method can have set of Rates
 */
class Rate extends AbstractModel implements ShippingTableRateInterface
{
    const ALGORITHM_SUM = 0;

    const ALGORITHM_MAX = 1;

    const ALGORITHM_MIN = 2;

    const MAX_VALUE = 99999999;

    const WEIGHT_TYPE_VOLUMETRIC = 1;
    const WEIGHT_TYPE_WEIGHT = 2;
    const WEIGHT_TYPE_MAX = 3;
    const WEIGHT_TYPE_MIN = 4;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    protected $_shippingTypes = [];

    protected $_existingShippingTypes = [];

    /**
     * @var ResourceModel\Rate\CollectionFactory
     */
    private $rateCollectionFactory;

    /**
     * @var ResourceModel\Method\CollectionFactory
     */
    private $methodCollectionFactory;

    /**
     * @var MethodFactory
     */
    private $methodFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Rate\ItemsTotalCalculator
     */
    private $itemsTotalCalculator;

    /**
     * @var Rate\ItemValidator
     */
    private $itemValidator;

    protected function _construct()
    {
        $this->_init(\Amasty\ShippingTableRates\Model\ResourceModel\Rate::class);
    }

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Model\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amasty\ShippingTableRates\Model\ResourceModel\Rate\CollectionFactory $rateCollectionFactory,
        \Amasty\ShippingTableRates\Model\ResourceModel\Method\CollectionFactory $methodCollectionFactory,
        \Amasty\ShippingTableRates\Model\MethodFactory $methodFactory,
        ConfigProvider $configProvider,
        Rate\ItemsTotalCalculator $itemsTotalCalculator,
        Rate\ItemValidator $itemValidator
    ) {
        $this->productRepository = $productRepository;
        $this->rateCollectionFactory = $rateCollectionFactory;
        // @TODO: it is over to get method collection factory
        $this->methodCollectionFactory = $methodCollectionFactory;
        $this->methodFactory = $methodFactory;
        $this->configProvider = $configProvider;
        $this->itemsTotalCalculator = $itemsTotalCalculator;
        $this->itemValidator = $itemValidator;
        parent::__construct($context, $coreRegistry);
    }
    /**
     * @TODO: move this method to independent service class
     * @param RateRequest $request
     * @param MethodCollection $collection
     *
     * @return array
     */
    public function findBy(RateRequest $request, MethodCollection $collection)
    {
        if (!$request->getAllItems()) {
            return [];
        }

        if ($collection->getSize() == 0) {
            return [];
        }

        $methodIds = [];
        foreach ($collection as $method) {
            $methodIds[] = $method->getId();
        }

        // calculate price and weight
        $allowFreePromo = $this->configProvider->isPromoAllowed();

        /** @var \Magento\Quote\Model\Quote\Item[] $items */
        $items = $request->getAllItems();

        $collectedTypes = [];
        $isFreeShipping = 0;

        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            if ($this->itemValidator->isShouldProcessChildren($item)) {
                foreach ($item->getChildren() as $child) {
                    $this->getShippingTypes($child);
                }
            } else {
                $this->getShippingTypes($item);
            }
            $address = $item->getAddress();

            if ($allowFreePromo && $address->getFreeShipping() === true) {
                $isFreeShipping = 1;
            }
        }

        $this->_shippingTypes = $this->_existingShippingTypes;
        $this->_shippingTypes[] = 0;

        $this->_shippingTypes = array_unique($this->_shippingTypes);
        $this->_existingShippingTypes = array_unique($this->_existingShippingTypes);

        $allCosts = [];
        $ratesTypes = [];

        /** @var \Amasty\ShippingTableRates\Model\ResourceModel\Rate\Collection $rateCollection */
        $rateCollection = $this->rateCollectionFactory->create();
        // @TODO: it is only for getting available shipping types for methods
        // @TODO: change to method in resource model with select + group by
        $ratesData = $rateCollection->getRatesWithFilters($methodIds, true);

        foreach ($ratesData as $singleRate) {
            $ratesTypes[$singleRate['method_id']][] = $singleRate['shipping_type'];
        }
        // @TODO: /end todo

        $rateCollection->reset();

        $intersectTypes = [];
        $freeTypes = [];
        /** @var MethodCollection $methodCollection */
        $methodCollection = $this->methodCollectionFactory->create();

        foreach ($ratesTypes as $key => $value) {
            $intersectTypes[$key] = array_intersect($this->_shippingTypes, $value);
            arsort($intersectTypes[$key]);
            $methodIds = [$key];
            $allTotals = $this->itemsTotalCalculator->execute($request, '0');

            // @TODO: we already have methods in $collection, it is over to load it again
            /** @var \Amasty\ShippingTableRates\Model\Method $method */
            $method = $methodCollection->getNewEmptyItem();
            $method->load($key);

            foreach ($intersectTypes[$key] as $shippingType) {
                // @TODO we dont need to call it again, if $shippingType == 0
                $totals = $this->itemsTotalCalculator->execute($request, $shippingType);

                if ($allTotals['qty'] > 0
                    && (!$this->configProvider->getDontSplit() || $allTotals['qty'] == $totals['qty'])
                ) {

                    if ($shippingType == 0) {
                        $totals = $allTotals;
                    }

                    /**
                     * avoid php opcache 7.0.33 bug
                     */
                    $allTotals['not_free_price'] = $allTotals['not_free_price'] - $totals['not_free_price'];
                    $allTotals['not_free_weight'] = $allTotals['not_free_weight'] - $totals['not_free_weight'];
                    $allTotals['not_free_volumetric'] =
                        $allTotals['not_free_volumetric'] - $totals['not_free_volumetric'];
                    $allTotals['not_free_qty'] = $allTotals['not_free_qty'] - $totals['not_free_qty'];
                    $allTotals['qty'] = $allTotals['qty'] - $totals['qty'];
                    $totals['not_free_weight'] = $this->getWeightForUse($totals, $method);
                    /** @var \Amasty\ShippingTableRates\Model\ResourceModel\Rate\Collection $rateCollection */
                    $rateCollection = $this->rateCollectionFactory->create();
                    $ratesData = $rateCollection->getRatesWithFilters(
                        $methodIds,
                        false,
                        [$request, $totals, $shippingType, $allowFreePromo]
                    );
                    $rateCollection->reset();

                    // @TODO: it returns costs as [$methodId => $cost] but it is always only one method,
                    // @TODO: because we load rates only for one method. Foreach is over
                    foreach ($this->calculateCosts($ratesData, $totals, $request, $shippingType) as $key => $cost) {
                        if (!($totals['not_free_qty'] > 0) && !($totals['qty'] > 0)) {
                            continue;
                        }

                        if (!($totals['not_free_qty'] > 0)) {
                            $cost['cost'] = 0;
                        }

                        if (empty($allCosts[$key])) {
                            $allCosts[$key]['cost'] = $cost['cost'];
                            $allCosts[$key]['time'] = $cost['time'];
                            $allCosts[$key]['name_delivery'] = $cost['name_delivery'];

                        } else {
                            $allCosts = $this->_setCostTime($method, $allCosts, $key, $cost);
                        }
                        $collectedTypes[$key][] = $shippingType;
                        $freeTypes[$key] = $method->getFreeTypes();
                    }
                }
            }
        }

        $allCosts = $this->_unsetUnnecessaryCosts($allCosts, $collectedTypes, $freeTypes);

        // @TODO i think that it is bug, because we load min and max rate for all methods in db
        // @TODO: need to use $collection (from params)
        $minRates = $methodCollection->hashMinRate();
        $maxRates = $methodCollection->hashMaxRate();

        $allCosts = $this->_includeMinMaxRates($allCosts, $maxRates, $minRates);
        $allCosts = $this->applyFreeShipping($allCosts, $isFreeShipping);

        return $allCosts;
    }

    /**
     * @param \Amasty\ShippingTableRates\Model\Rate $method
     * @param array $allCosts
     * @param int $key
     * @param array $cost
     *
     * @return array
     */
    protected function _setCostTime($method, $allCosts, $key, $cost)
    {
        switch ($method->getSelectRate()) {
            case self::ALGORITHM_MAX:
                if ($allCosts[$key]['cost'] < $cost['cost']) {
                    $allCosts[$key]['cost'] = $cost['cost'];
                    $allCosts[$key]['time'] = $cost['time'];
                    $allCosts[$key]['name_delivery'] = $cost['name_delivery'];
                }
                break;
            case self::ALGORITHM_MIN:
                if ($allCosts[$key]['cost'] > $cost['cost']) {
                    $allCosts[$key]['cost'] = $cost['cost'];
                    $allCosts[$key]['time'] = $cost['time'];
                    $allCosts[$key]['name_delivery'] = $cost['name_delivery'];
                }
                break;
            default:
                $allCosts[$key]['cost'] += $cost['cost'];
                $allCosts[$key]['name_delivery'] = '';

                if ($cost['time'] > $allCosts[$key]['time']) {
                    $allCosts[$key]['time'] = $cost['time'];
                }
        }

        return $allCosts;
    }

    /**
     * @param array $allCosts
     * @param array $maxRates
     * @param array $minRates
     *
     * @return array
     */
    protected function _includeMinMaxRates($allCosts, $maxRates, $minRates)
    {
        foreach ($allCosts as $key => $rate) {
            if ($maxRates[$key] != '0.00' && $maxRates[$key] < $rate['cost']) {
                $allCosts[$key]['cost'] = $maxRates[$key];
            }

            if ($minRates[$key] != '0.00' && $minRates[$key] > $rate['cost']) {
                $allCosts[$key]['cost'] = $minRates[$key];
            }
        }

        return $allCosts;
    }

    /**
     * @param array $allCosts
     * @param int $isFreeShipping
     *
     * @return array
     */
    protected function applyFreeShipping($allCosts, $isFreeShipping)
    {
        if ($isFreeShipping) {
            foreach ($allCosts as $key => $rate) {
                $allCosts[$key]['cost'] = 0;
            }
        }

        return $allCosts;
    }

    /**
     * @param array $allCosts
     * @param array $collectedTypes
     * @param array $freeTypes
     *
     * @return array
     */
    protected function _unsetUnnecessaryCosts($allCosts, $collectedTypes, $freeTypes)
    {
        //do not show method if quote has "unsuitable" items
        foreach ($allCosts as $key => $cost) {
            //1.if the method contains rate with type == All
            if (in_array('0', $collectedTypes[$key])) {
                continue;
            }
            //2.if the method rates contain types for every items in quote
            $extraTypes = array_diff($this->_existingShippingTypes, $collectedTypes[$key]);
            if (!$extraTypes) {
                continue;
            }
            //3.if the method free types contain types for every item didn't pass (2)
            if (!array_diff($extraTypes, $freeTypes[$key])) {
                continue;
            }

            //else — do not show the method;
            unset($allCosts[$key]);
        }

        return $allCosts;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    protected function getShippingTypes($item)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($item->getProductId());

        if ($product->getAmShippingType()) {
            $this->_existingShippingTypes[] = $product->getAmShippingType();
        } else {
            $this->_existingShippingTypes[] = 0;
        }
    }

    /**
     * @param array $allRates
     * @param array $totals
     * @param RateRequest $request
     * @param int $shippingType
     *
     * @return array
     */
    protected function calculateCosts($allRates, $totals, $request, $shippingType)
    {
        $shippingFlatParams = ['country', 'state'];
        $shippingRangeParams = ['price', 'qty', 'weight'];

        $minCounts = [];   // min empty values counts per method
        $results = [];
        foreach ($allRates as $rate) {
            $emptyValuesCount = 0;

            if (empty($rate['shipping_type'])) {
                $emptyValuesCount++;
            }

            foreach ($shippingFlatParams as $param) {
                if (empty($rate[$param])) {
                    $emptyValuesCount++;
                }
            }

            foreach ($shippingRangeParams as $param) {
                if ((ceil($rate[$param . '_from']) == 0) && (ceil($rate[$param . '_to']) == self::MAX_VALUE)) {
                    $emptyValuesCount++;
                }
            }

            if (empty($rate['zip_from']) && empty($rate['zip_to'])) {
                $emptyValuesCount++;
            }
            $id = $rate['method_id'];

            if (!$totals['not_free_price'] && !$totals['not_free_qty']
                && (!$totals['not_free_weight'] || !$totals['volumetric'])) {
                $cost = 0;
            } else {
                $cost = $rate['cost_base'] + ($totals['not_free_price'] * $rate['cost_percent'] / 100)
                    + ($totals['not_free_qty'] * $rate['cost_product'])
                    + ($totals['not_free_weight'] * $rate['cost_weight']);
            }

            if ((empty($minCounts[$id]) && empty($results[$id]))
                || (($minCounts[$id] > $emptyValuesCount
                    || ($minCounts[$id] == $emptyValuesCount && $cost > $results[$id]['cost']))
                    && ($rate['shipping_type'] != 0 || $results[$id]['shipping_type'] == 0))
                || ($rate['city'] != $results[$id]['city']
                    && ($rate['country'] == $results[$id]['country']
                        && $rate['state'] == $results[$id]['state']))
            ) {
                $minCounts[$id] = $emptyValuesCount;
                $results[$id]['cost'] = $cost;
                $results[$id]['time'] = $rate['time_delivery'];
                $results[$id]['shipping_type'] = $rate['shipping_type'];
                $results[$id]['name_delivery'] = $rate['name_delivery'];
                $results[$id]['city'] = $rate['city'];
                $results[$id]['country'] = $rate['country'];
                $results[$id]['state'] = $rate['state'];
            }
        }

        return $results;
    }

    /**
     * @param $totals
     * @param $method
     * @return float
     */
    public function getWeightForUse($totals, $method)
    {
        switch ($method->getWeightType()) {
            case self::WEIGHT_TYPE_WEIGHT:
                return (float)$totals['not_free_weight'];
            case self::WEIGHT_TYPE_VOLUMETRIC:
                return (float)$totals['not_free_volumetric'];
            case self::WEIGHT_TYPE_MIN:
                return (float)min($totals['not_free_volumetric'], $totals['not_free_weight']);
            default: // self::WEIGHT_TYPE_MAX
                return (float)max($totals['not_free_volumetric'], $totals['not_free_weight']);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMethodId()
    {
        return $this->_getData(ShippingTableRateInterface::METHOD_ID);
    }

    /**
     * @inheritdoc
     */
    public function setMethodId($methodId)
    {
        $this->setData(ShippingTableRateInterface::METHOD_ID, $methodId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountry()
    {
        return $this->_getData(ShippingTableRateInterface::COUNTRY);
    }

    /**
     * @inheritdoc
     */
    public function setCountry($country)
    {
        $this->setData(ShippingTableRateInterface::COUNTRY, $country);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        return $this->_getData(ShippingTableRateInterface::STATE);
    }

    /**
     * @inheritdoc
     */
    public function setState($state)
    {
        $this->setData(ShippingTableRateInterface::STATE, $state);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getZipFrom()
    {
        return $this->_getData(ShippingTableRateInterface::ZIP_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setZipFrom($zipFrom)
    {
        $this->setData(ShippingTableRateInterface::ZIP_FROM, $zipFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getZipTo()
    {
        return $this->_getData(ShippingTableRateInterface::ZIP_TO);
    }

    /**
     * @inheritdoc
     */
    public function setZipTo($zipTo)
    {
        $this->setData(ShippingTableRateInterface::ZIP_TO, $zipTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriceFrom()
    {
        return $this->_getData(ShippingTableRateInterface::PRICE_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setPriceFrom($priceFrom)
    {
        $this->setData(ShippingTableRateInterface::PRICE_FROM, $priceFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriceTo()
    {
        return $this->_getData(ShippingTableRateInterface::PRICE_TO);
    }

    /**
     * @inheritdoc
     */
    public function setPriceTo($priceTo)
    {
        $this->setData(ShippingTableRateInterface::PRICE_TO, $priceTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWeightFrom()
    {
        return $this->_getData(ShippingTableRateInterface::WEIGHT_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setWeightFrom($weightFrom)
    {
        $this->setData(ShippingTableRateInterface::WEIGHT_FROM, $weightFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWeightTo()
    {
        return $this->_getData(ShippingTableRateInterface::WEIGHT_TO);
    }

    /**
     * @inheritdoc
     */
    public function setWeightTo($weightTo)
    {
        $this->setData(ShippingTableRateInterface::WEIGHT_TO, $weightTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQtyFrom()
    {
        return $this->_getData(ShippingTableRateInterface::QTY_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setQtyFrom($qtyFrom)
    {
        $this->setData(ShippingTableRateInterface::QTY_FROM, $qtyFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQtyTo()
    {
        return $this->_getData(ShippingTableRateInterface::QTY_TO);
    }

    /**
     * @inheritdoc
     */
    public function setQtyTo($qtyTo)
    {
        $this->setData(ShippingTableRateInterface::QTY_TO, $qtyTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShippingType()
    {
        return $this->_getData(ShippingTableRateInterface::SHIPPING_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setShippingType($shippingType)
    {
        $this->setData(ShippingTableRateInterface::SHIPPING_TYPE, $shippingType);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCostBase()
    {
        return $this->_getData(ShippingTableRateInterface::COST_BASE);
    }

    /**
     * @inheritdoc
     */
    public function setCostBase($costBase)
    {
        $this->setData(ShippingTableRateInterface::COST_BASE, $costBase);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCostPercent()
    {
        return $this->_getData(ShippingTableRateInterface::COST_PERCENT);
    }

    /**
     * @inheritdoc
     */
    public function setCostPercent($costPercent)
    {
        $this->setData(ShippingTableRateInterface::COST_PERCENT, $costPercent);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCostProduct()
    {
        return $this->_getData(ShippingTableRateInterface::COST_PRODUCT);
    }

    /**
     * @inheritdoc
     */
    public function setCostProduct($costProduct)
    {
        $this->setData(ShippingTableRateInterface::COST_PRODUCT, $costProduct);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCostWeight()
    {
        return $this->_getData(ShippingTableRateInterface::COST_WEIGHT);
    }

    /**
     * @inheritdoc
     */
    public function setCostWeight($costWeight)
    {
        $this->setData(ShippingTableRateInterface::COST_WEIGHT, $costWeight);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTimeDelivery()
    {
        return $this->_getData(ShippingTableRateInterface::TIME_DELIVERY);
    }

    /**
     * @inheritdoc
     */
    public function setTimeDelivery($timeDelivery)
    {
        $this->setData(ShippingTableRateInterface::TIME_DELIVERY, $timeDelivery);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNumZipFrom()
    {
        return $this->_getData(ShippingTableRateInterface::NUM_ZIP_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setNumZipFrom($numZipFrom)
    {
        $this->setData(ShippingTableRateInterface::NUM_ZIP_FROM, $numZipFrom);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNumZipTo()
    {
        return $this->_getData(ShippingTableRateInterface::NUM_ZIP_TO);
    }

    /**
     * @inheritdoc
     */
    public function setNumZipTo($numZipTo)
    {
        $this->setData(ShippingTableRateInterface::NUM_ZIP_TO, $numZipTo);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCity()
    {
        return $this->_getData(ShippingTableRateInterface::CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity($city)
    {
        $this->setData(ShippingTableRateInterface::CITY, $city);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNameDelivery()
    {
        return $this->_getData(ShippingTableRateInterface::NAME_DELIVERY);
    }

    /**
     * @inheritdoc
     */
    public function setNameDelivery($nameDelivery)
    {
        $this->setData(ShippingTableRateInterface::NAME_DELIVERY, $nameDelivery);

        return $this;
    }
}
