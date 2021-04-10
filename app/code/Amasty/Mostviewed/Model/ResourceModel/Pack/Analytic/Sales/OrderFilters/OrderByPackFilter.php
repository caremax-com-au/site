<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\OrderFilters;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\PackHistoryTable;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class OrderByPackFilter implements OrderFilterInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Collection $collection, string $value): void
    {
        $collection->getSelect()->join(
            ['pack_sales' => $this->resourceConnection->getTableName(PackHistoryTable::TABLE_NAME)],
            sprintf('pack_sales.%s = main_table.entity_id', PackHistoryTable::ORDER_COLUMN),
            []
        )->where(sprintf('pack_sales.%s = ?', PackHistoryTable::PACK_COLUMN), $value);
    }
}
