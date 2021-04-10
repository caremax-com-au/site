<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup\UpgradeSchema;

use Amasty\Mostviewed\Api\Data\GroupInterface;
use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\ResourceModel\Pack;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddCurrentCategory
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(GroupInterface::TABLE_NAME);
        $setup->getConnection()->addColumn(
            $table,
            GroupInterface::CURRENT_CATEGORY,
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Products from current category only toggle'
            ]
        );
    }
}
