<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup\UpgradeSchema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Magento\Framework\DB\Ddl\Table;

class OptimizeIndexTable
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(RuleIndex::MAIN_TABLE);
        $setup->getConnection()->dropIndex($table, $setup->getIdxName(
            $table,
            [
                'rule_id',
                'product_id'
            ],
            true
        ));

        $setup->getConnection()->dropIndex($table, $setup->getIdxName($table, ['product_id']));

        $setup->getConnection()->changeColumn(
            $table,
            RuleIndex::POSITION,
            RuleIndex::POSITION,
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'default'  => false,
                'length' => 30,
                'comment'  => 'Position where label need displayed'
            ]
        );

        $setup->getConnection()->changeColumn(
            $table,
            RuleIndex::RELATION,
            RuleIndex::RELATION,
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => false,
                'length' => 12,
                'comment'  => 'Type of rule'
            ]
        );

        $setup->getConnection()->addIndex(
            $table,
            $setup->getIdxName(
                $table,
                [
                    RuleIndex::ENTITY_ID,
                    RuleIndex::RELATION,
                    RuleIndex::POSITION,
                    RuleIndex::STORE_ID
                ]
            ),
            [
                RuleIndex::ENTITY_ID,
                RuleIndex::POSITION,
                RuleIndex::RELATION,
                RuleIndex::STORE_ID
            ]
        );
    }
}
