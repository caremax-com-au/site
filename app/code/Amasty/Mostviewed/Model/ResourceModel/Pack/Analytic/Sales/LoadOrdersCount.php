<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales;

use Magento\Framework\App\ResourceConnection;

class LoadOrdersCount
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetAggregatedTable
     */
    private $getAggregatedTable;

    public function __construct(
        ResourceConnection  $resourceConnection,
        GetAggregatedTable $getAggregatedTable
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getAggregatedTable = $getAggregatedTable;
    }

    public function execute(int $packId): int
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->getAggregatedTable->execute(),
            [GetAggregatedTable::COUNT_COLUMN]
        )->where(sprintf('%s = ?', GetAggregatedTable::PACK_COLUMN), $packId);

        return (int) $this->resourceConnection->getConnection()->fetchOne($select);
    }
}
