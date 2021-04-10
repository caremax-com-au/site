<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


declare(strict_types=1);

namespace Amasty\SeoToolKit\Setup;

use Amasty\SeoToolKit\Setup\UpgradeSchema as Operation;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\CreateRedirect
     */
    private $createRedirect;

    public function __construct(
        Operation\CreateRedirect $createRedirect
    ) {
        $this->createRedirect = $createRedirect;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.17.0', '<')) {
            $this->createRedirect->execute($setup);
        }

        $setup->endSetup();
    }
}
