<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


declare(strict_types=1);

namespace Amasty\SeoToolKit\Setup\UpgradeSchema;

use Amasty\SeoToolKit\Api\Data\RedirectInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Store\Model\Store;

class CreateRedirect
{
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createRedirectTable($setup);
        $this->createStoreTable($setup);
    }

    private function createRedirectTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(RedirectInterface::TABLE_NAME);
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                RedirectInterface::REDIRECT_ID,
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Redirect Id'
            )
            ->addColumn(
                RedirectInterface::STATUS,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => false],
                'Redirect Status'
            )
            ->addColumn(
                RedirectInterface::REQUEST_PATH,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Request Path'
            )
            ->addColumn(
                RedirectInterface::TARGET_PATH,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Target Path'
            )
            ->addColumn(
                RedirectInterface::REDIRECT_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'identity' => false, 'default' => 302],
                'Redirect Type'
            )
            ->addColumn(
                RedirectInterface::UNDEFINED_PAGE_ONLY,
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => false],
                'Used for 404 Pages'
            )
            ->addColumn(
                RedirectInterface::DESCRIPTION,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Description'
            )
            ->addColumn(
                RedirectInterface::PRIORITY,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 1],
                'Priority'
            )
            ->addIndex(
                $setup->getIdxName(
                    $setup->getTable(RedirectInterface::TABLE_NAME),
                    [RedirectInterface::REDIRECT_ID],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [RedirectInterface::REDIRECT_ID],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            );

        $setup->getConnection()->createTable($table);
    }

    private function createStoreTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(RedirectInterface::STORE_TABLE_NAME);
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                RedirectInterface::REDIRECT_ID,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Redirect Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Store Id'
            )
            ->addForeignKey(
                $setup->getFkName(
                    $tableName,
                    RedirectInterface::REDIRECT_ID,
                    RedirectInterface::TABLE_NAME,
                    RedirectInterface::REDIRECT_ID
                ),
                RedirectInterface::REDIRECT_ID,
                $setup->getTable(RedirectInterface::TABLE_NAME),
                RedirectInterface::REDIRECT_ID,
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName($tableName, Store::STORE_ID, 'store', Store::STORE_ID),
                Store::STORE_ID,
                $setup->getTable('store'),
                Store::STORE_ID,
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $setup->getConnection()->createTable($table);
    }
}
