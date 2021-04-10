<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\Finder;

class PackResult
{
    /**
     * @var int|null
     */
    private $packId;

    /**
     * @var int
     */
    private $packQty = 0;

    /**
     * @var array
     */
    private $items = [];

    public function addItem(int $itemId, float $qty): void
    {
        $this->items[$itemId] = $qty;
    }

    public function getItemQty(int $itemId): float
    {
        return $this->items[$itemId] ?? 0;
    }

    public function decreaseQty(int $itemId, float $qty): void
    {
        if (isset($this->items[$itemId])) {
            $this->items[$itemId] -= $qty;
        }
    }

    public function getPackId(): ?int
    {
        return $this->packId;
    }

    public function setPackId(int $packId): void
    {
        $this->packId = $packId;
    }

    public function getPackQty(): int
    {
        return $this->packQty;
    }

    public function setPackQty(int $packQty): void
    {
        $this->packQty = $packQty;
    }
}
