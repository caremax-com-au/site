<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\Cart\Discount;

use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class CalculateDiscount
{

    /**
     * @var GetAppliedPacks
     */
    private $getAppliedPacks;

    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    public function __construct(
        GetAppliedPacks $getAppliedPacks,
        PackRepositoryInterface $packRepository
    ) {
        $this->getAppliedPacks = $getAppliedPacks;
        $this->packRepository = $packRepository;
    }

    public function execute(AbstractItem $item): array
    {
        $packsForItem = [];
        $productId = (int) $item->getProduct()->getId();
        foreach ($this->getAppliedPacks->execute($item->getQuote()) as $appliedPack) {
            $pack = $this->packRepository->getById($appliedPack->getPackId());
            $childIds = explode(',', $pack->getProductIds());
            if (in_array($productId, $childIds)
                || (in_array($productId, $pack->getParentIds()) && $pack->getApplyForParent())
            ) {
                $itemQtyAvailableForDiscount = $appliedPack->getItemQty($productId);
                $itemQtyInCart = $item->getTotalQty();
                $itemQtyForDiscount = min($itemQtyAvailableForDiscount, $itemQtyInCart);
                $appliedPack->decreaseQty($productId, $itemQtyForDiscount);
                $packsForItem[$pack->getPackId()] = $itemQtyForDiscount;
            }
        }

        return $packsForItem;
    }
}
