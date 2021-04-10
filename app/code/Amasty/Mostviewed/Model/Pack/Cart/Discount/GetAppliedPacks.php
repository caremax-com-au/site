<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\Cart\Discount;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\Customer\GroupValidator;
use Amasty\Mostviewed\Model\Pack\Finder\GetCountInPool;
use Amasty\Mostviewed\Model\Pack\Finder\ItemPool;
use Amasty\Mostviewed\Model\Pack\Finder\ItemPoolFactory;
use Amasty\Mostviewed\Model\Pack\Finder\PackResult;
use Magento\Quote\Api\Data\CartInterface;

class GetAppliedPacks
{
    /**
     * @var array
     */
    private $appliedPacks = [];

    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var GroupValidator
     */
    private $groupValidator;

    /**
     * @var GetCountInPool
     */
    private $getCountInPool;

    /**
     * @var ItemPoolFactory
     */
    private $itemPoolFactory;

    public function __construct(
        PackRepositoryInterface $packRepository,
        GroupValidator $groupValidator,
        GetCountInPool $getCountInPool,
        ItemPoolFactory $itemPoolFactory
    ) {
        $this->packRepository = $packRepository;
        $this->groupValidator = $groupValidator;
        $this->getCountInPool = $getCountInPool;
        $this->itemPoolFactory = $itemPoolFactory;
    }

    /**
     * @param CartInterface $quote
     * @return PackResult[]
     */
    public function execute(CartInterface $quote): array
    {
        if (!isset($this->appliedPacks[$quote->getId()])) {
            $itemPool = $this->convertQuoteToPool($quote);
            $this->appliedPacks[$quote->getId()] = $this->resolvePacks($itemPool, (int) $quote->getStoreId());
        }

        return $this->appliedPacks[$quote->getId()];
    }

    /**
     * @param ItemPool $itemPool
     * @param int $storeId
     * @return PackResult[]
     */
    private function resolvePacks(ItemPool $itemPool, int $storeId): array
    {
        $appliedPacks = [];
        foreach ($this->findAllAvailablePacks($itemPool, $storeId) as $pack) {
            $packResult = $this->getCountInPool->execute($pack, $itemPool);
            if ($packResult->getPackQty()) {
                $appliedPacks[$packResult->getPackId()] = $packResult;
            }
        }

        return $appliedPacks;
    }

    /**
     * @param ItemPool $itemPool
     * @param int $storeId
     * @return PackInterface[]
     */
    private function findAllAvailablePacks(ItemPool $itemPool, int $storeId): array
    {
        $allProductIds = [];
        foreach ($itemPool->getItems() as $item) {
            $allProductIds[] = $item->getId();
        }

        $packsAsChild = $this->packRepository->getPacksByChildProductsAndStore($allProductIds, $storeId) ?: [];
        $packsAsParent = $this->packRepository->getPacksByParentProductsAndStore($allProductIds, $storeId) ?: [];

        /** @var PackInterface[] $packsMerged */
        $packsMerged = [];
        foreach (array_merge($packsAsChild, $packsAsParent) as $pack) {
            if ($this->groupValidator->validate($pack)) {
                $packsMerged[$pack->getPackId()] = $pack;
            }
        }
        usort($packsMerged, function ($packA, $packB) {
            return $packA->getPackId() > $packB->getPackId();
        });

        return $packsMerged;
    }

    private function convertQuoteToPool(CartInterface $quote): ItemPool
    {
        /** @var ItemPool $itemPool */
        $itemPool = $this->itemPoolFactory->create();

        foreach ($quote->getAllItems() as $quoteItem) {
            $itemPool->createItem((int) $quoteItem->getProduct()->getId(), $quoteItem->getTotalQty());
        }

        return $itemPool;
    }
}
