<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Setup\Operations;

use Amasty\Stripe\Api\Data\CustomerInterface;
use Amasty\Stripe\Model\ResourceModel\Customer;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo200
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createStripeAccountsTable($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createStripeAccountsTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(Customer::TABLE_NAME)
        )->addColumn(
            CustomerInterface::ENTITY_ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            CustomerInterface::CUSTOMER_ID,
            Table::TYPE_INTEGER,
            null,
            ['primary' => true, 'unsigned' => true, 'nullable' => false],
            'Magento customer id'
        )->addColumn(
            CustomerInterface::STRIPE_CUSTOMER_ID,
            Table::TYPE_TEXT,
            255,
            [],
            'Stripe customer id'
        )->addColumn(
            CustomerInterface::STRIPE_ACCOUNT_ID,
            Table::TYPE_TEXT,
            255,
            [],
            'Stripe account id of business owner'
        )->addForeignKey(
            $setup->getFkName(
                Customer::TABLE_NAME,
                CustomerInterface::CUSTOMER_ID,
                'customer_entity',
                'entity_id'
            ),
            CustomerInterface::CUSTOMER_ID,
            $setup->getTable('customer_entity'),
            CustomerInterface::ENTITY_ID,
            Table::ACTION_CASCADE
        )->setComment(
            'Amasty stripe customers'
        );

        $setup->getConnection()->createTable($table);
    }
}
