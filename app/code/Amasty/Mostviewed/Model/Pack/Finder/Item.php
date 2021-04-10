<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\Finder;

class Item
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $qty;

    public function init(int $id, float $qty): void
    {
        $this->id = $id;
        $this->qty = $qty;
    }

    public function addQty(float $qty): void
    {
        $this->qty += $qty;
    }

    public function decrease(float $qty): void
    {
        $this->qty -= $qty;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQty(): float
    {
        return $this->qty;
    }
}
