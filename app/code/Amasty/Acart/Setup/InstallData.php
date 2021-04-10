<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Setup;

use Amasty\Base\Model\MagentoVersion;
use Magento\Email\Model\Template;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        MagentoVersion $magentoVersion
    ) {
        $this->appState = $appState;
        $this->productMetaData = $productMetaData;
        $this->magentoVersion = $magentoVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup->createMigrationSetup();
        $setup->startSetup();

        $this->appState->emulateAreaCode('frontend', [$this, 'createEmailTemplate']);
        $installer->doUpdateClassAliases();

        $setup->endSetup();
    }

    /**
     * @return void
     */
    public function createEmailTemplate()
    {
        $templateCode = 'amasty_acart_template';

        $template = ObjectManager::getInstance()
            ->create(Template::class);

        $template->setForcedArea($templateCode);

        $template->loadDefault($templateCode);

        $template->setData('orig_template_code', $templateCode);

        $template->setData('template_variables', \Zend_Json::encode($template->getVariablesOptionArray(true)));

        $template->setData('template_code', 'Amasty: Abandoned Cart Reminder');

        $template->setTemplateType(Template::TYPE_HTML);

        $template->setId(null);

        if (version_compare($this->magentoVersion->get(), '2.3.4', '>=')) {
            $template->setIsLegacy(1);
        }

        $template->save();
    }
}
