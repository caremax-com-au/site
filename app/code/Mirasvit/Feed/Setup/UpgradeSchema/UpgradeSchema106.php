<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.30
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Setup\UpgradeSchema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\Feed\Api\Data\RuleInterface;

class UpgradeSchema106 implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()->dropColumn($setup->getTable(RuleInterface::TABLE_NAME), 'type');
        $setup->getConnection()->dropColumn($setup->getTable(RuleInterface::TABLE_NAME), 'actions_serialized');
        $setup->getConnection()->dropColumn($setup->getTable(RuleInterface::TABLE_NAME), 'created_at');
        $setup->getConnection()->dropColumn($setup->getTable(RuleInterface::TABLE_NAME), 'updated_at');
    }
}
