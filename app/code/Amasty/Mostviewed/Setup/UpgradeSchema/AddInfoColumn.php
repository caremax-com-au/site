<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup\UpgradeSchema;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\ResourceModel\Pack;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddInfoColumn
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(Pack::PACK_TABLE);
        $setup->getConnection()->addColumn(
            $table,
            PackInterface::PRODUCTS_INFO,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'default' => false,
                'comment' => 'Info about bundle pack products'
            ]
        );
    }
}
