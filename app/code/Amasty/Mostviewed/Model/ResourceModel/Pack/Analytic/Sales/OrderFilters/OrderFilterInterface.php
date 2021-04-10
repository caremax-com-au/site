<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\OrderFilters;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

interface OrderFilterInterface
{
    public function execute(Collection $collection, string $value): void;
}
