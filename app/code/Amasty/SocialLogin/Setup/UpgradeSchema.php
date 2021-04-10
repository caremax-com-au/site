<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var UpgradeSchema\AddLoginDataTable
     */
    private $addLoginDataTable;

    /**
     * @var UpgradeSchema\ChangeFieldType
     */
    private $changeFieldType;

    public function __construct(
        \Amasty\SocialLogin\Setup\UpgradeSchema\AddLoginDataTable $addLoginDataTable,
        \Amasty\SocialLogin\Setup\UpgradeSchema\ChangeFieldType $changeFieldType
    ) {
        $this->addLoginDataTable = $addLoginDataTable;
        $this->changeFieldType = $changeFieldType;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addLoginDataTable->execute($setup);
        }
        if (version_compare($context->getVersion(), '1.5.1', '<')) {
            $this->changeFieldType->execute($setup);
        }

        $setup->endSetup();
    }
}
