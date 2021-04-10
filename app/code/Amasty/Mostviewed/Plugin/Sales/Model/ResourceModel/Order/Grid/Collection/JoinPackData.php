<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Plugin\Sales\Model\ResourceModel\Order\Grid\Collection;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Sales\GetAggregatedByOrderTable;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class JoinPackData
{
    const JOINED_PACK_DATA_FLAG = 'pack_data_joined';

    /**
     * @var GetAggregatedByOrderTable
     */
    private $getAggregatedByOrderTable;

    public function __construct(GetAggregatedByOrderTable $getAggregatedByOrderTable)
    {
        $this->getAggregatedByOrderTable = $getAggregatedByOrderTable;
    }

    public function beforeLoad(Collection $subject): void
    {
        if (!$subject->isLoaded() && !$subject->hasFlag(self::JOINED_PACK_DATA_FLAG)) {
            $subject->setFlag(self::JOINED_PACK_DATA_FLAG, true);
            $subject->getSelect()->joinLeft(
                [GetAggregatedByOrderTable::VIEW_NAME => $this->getAggregatedByOrderTable->execute()],
                sprintf(
                    '%s.%s = main_table.entity_id',
                    GetAggregatedByOrderTable::VIEW_NAME,
                    GetAggregatedByOrderTable::ORDER_COLUMN
                ),
                [
                    'mostviewed_bundles' => GetAggregatedByOrderTable::PACK_NAMES_COLUMN,
                    'mostviewed_includes_bundles' => $subject->getConnection()->getCheckSql(
                        sprintf(
                            '%s.%s IS NOT NULL',
                            GetAggregatedByOrderTable::VIEW_NAME,
                            GetAggregatedByOrderTable::ORDER_COLUMN
                        ),
                        1,
                        0
                    )
                ]
            );
        }
    }
}
