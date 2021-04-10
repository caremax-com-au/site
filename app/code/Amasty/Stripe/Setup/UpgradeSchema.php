<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Setup;

use Amasty\Stripe\Setup\Operations\UpgradeTo200;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var UpgradeTo200
     */
    private $upgradeTo200;

    public function __construct(UpgradeTo200 $upgradeTo200)
    {
        $this->upgradeTo200 = $upgradeTo200;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->upgradeTo200->execute($setup);
        }

        $setup->endSetup();
    }
}
