<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\Finder;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\OptionSource\ApplyCondition;

class GetCountInPool
{
    /**
     * @var PackResultFactory
     */
    private $packResultFactory;

    public function __construct(PackResultFactory $packResultFactory)
    {
        $this->packResultFactory = $packResultFactory;
    }

    public function execute(PackInterface $pack, ItemPool $itemPool): PackResult
    {
        /** @var PackResult $packResult */
        $packResult = $this->packResultFactory->create();
        $packId = (int) $pack->getPackId();

        $packResult->setPackId($packId);

        $parentIds = $pack->getParentIds();

        $parentPackQty = 0;
        foreach ($parentIds as $parentId) {
            $parentPackQty += $itemPool->getQty((int) $parentId);
        }

        if (!$parentPackQty) {
            return $packResult;
        }

        $childProductIds = explode(',', $pack->getProductIds());
        $childPackQty = 0;
        foreach ($childProductIds as $childProductId) {
            $childProductId = (int) $childProductId;
            $packQty = floor(
                $itemPool->getQty((int) $childProductId) / $pack->getChildProductQty($childProductId)
            );
            $availablePacksQty[] = $packQty;
            $childPackQty += $packQty;
        }

        if ($pack->getApplyCondition() === ApplyCondition::ALL_PRODUCTS) {
            $availablePacksQty[] = $parentPackQty;
            $packQty = min($availablePacksQty);
        } else {
            $packQty = min($parentPackQty, $childPackQty);
        }
        $packQty = (int) floor($packQty);

        $packResult->setPackQty($packQty);

        if ($packQty) {
            $parentId = (int) reset($parentIds);
            while ($packQty > 0 && $parentId) {
                $leftQty = $itemPool->getQty($parentId);
                $qtyToDecrease = min($packQty, $leftQty);
                $packQty -= $qtyToDecrease;
                $packResult->addItem($parentId, $qtyToDecrease);
                $itemPool->decrease($parentId, $qtyToDecrease);
                $parentId = (int) next($parentIds);
            }

            foreach ($childProductIds as $childProductId) {
                $childId = (int) $childProductId;
                $availablePackQty = floor(
                    $itemPool->getQty($childId) / $pack->getChildProductQty($childId)
                );
                $availablePackQty = min($availablePackQty, $packResult->getPackQty());
                $availableChildQty = $availablePackQty * $pack->getChildProductQty($childId);
                $packResult->addItem($childId, $availableChildQty);
                $itemPool->decrease($childId, $availableChildQty);
            }
        }

        return $packResult;
    }
}
