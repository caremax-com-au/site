<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CronScheduler
 */


namespace Amasty\CronScheduler\Setup;

use Amasty\CronScheduler\Setup\Operation\UpgradeTo104;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var UpgradeTo104
     */
    private $upgradeTo104;

    public function __construct(
        UpgradeTo104 $upgradeTo104
    ) {
        $this->upgradeTo104 = $upgradeTo104;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (empty($context->getVersion()) || version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->upgradeTo104->upgradeData($setup);
        }

        $setup->endSetup();
    }
}
