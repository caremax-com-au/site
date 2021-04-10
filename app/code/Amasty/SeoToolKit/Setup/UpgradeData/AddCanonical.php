<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


declare(strict_types=1);

namespace Amasty\SeoToolKit\Setup\UpgradeData;

use Amasty\SeoToolKit\Model\RegistryConstants;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddCanonical
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function execute()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            RegistryConstants::AMTOOLKIT_CANONICAL,
            [
                'type' => 'varchar',
                'label' => 'Canonical Link',
                'input' => 'text',
                'required' => false,
                'sort_order' => 100,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'visible' => false,
                'group' => 'Search Engine Optimization',
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            RegistryConstants::AMTOOLKIT_ROBOTS,
            [
                'type' => 'varchar',
                'label' => 'Robots',
                'input' => 'select',
                'source' => \Amasty\SeoToolKit\Model\Source\Eav\Robots::class,
                'required' => false,
                'sort_order' => 110,
                'default' => 0,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => false,
                'group' => 'Search Engine Optimization',
            ]
        );
    }
}
