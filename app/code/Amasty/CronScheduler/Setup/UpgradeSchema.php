<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CronScheduler
 */


namespace Amasty\CronScheduler\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\UpgradeTo104
     */
    private $upgradeTo104;

    public function __construct(
        Operation\UpgradeTo104 $upgradeTo104
    ) {
        $this->upgradeTo104 = $upgradeTo104;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->upgradeTo104->upgradeSchema($setup);
        }
        $setup->endSetup();
    }
}
