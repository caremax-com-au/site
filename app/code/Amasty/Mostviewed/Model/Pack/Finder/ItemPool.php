<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\Finder;

class ItemPool
{
    /**
     * @var Item[]
     */
    private $pool = [];

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    public function __construct(ItemFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
    }

    public function createItem(int $id, float $qty): void
    {
        /** @var Item $item */
        $item = $this->itemFactory->create();
        $item->init($id, $qty);
        $this->addItem($item);
    }

    public function addItem(Item $item): void
    {
        if (isset($this->pool[$item->getId()])) {
            $this->pool[$item->getId()]->addQty($item->getQty());
        } else {
            $this->pool[$item->getId()] = $item;
        }
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->pool;
    }

    public function decrease(int $itemId, float $qty): void
    {
        if (isset($this->pool[$itemId])) {
            $this->pool[$itemId]->decrease($qty);
        }
    }

    public function getQty(int $itemId): float
    {
        return isset($this->pool[$itemId]) ? $this->pool[$itemId]->getQty() : 0;
    }
}
