<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Plugin\Sales\Model\ResourceModel\Order\Grid\Collection;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\OrderFilters\OrderFilterInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class ApplyPackFilters
{
    /**
     * @var OrderFilterInterface[]
     */
    private $filterPool;

    public function __construct(array $filterPool)
    {
        $this->filterPool = $filterPool;
    }

    public function aroundAddFieldToFilter(
        Collection $subject,
        callable $proceed,
        string $field,
        $condition = null
    ): Collection {
        if (isset($this->filterPool[$field]) && $condition) {
            $this->filterPool[$field]->execute($subject, array_shift($condition));
        } else {
            $proceed($field, $condition);
        }

        return $subject;
    }
}
