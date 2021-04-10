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



namespace Mirasvit\Feed\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Mirasvit\Feed\Model\Config;
use Mirasvit\Feed\Model\TemplateFactory;
use Mirasvit\Feed\Repository\RuleRepository;
use Mirasvit\Feed\Service\ImportService;

class InstallData implements InstallDataInterface
{
    private $templateFactory;

    private $importService;

    private $ruleRepository;

    private $config;

    public function __construct(
        TemplateFactory $templateFactory,
        ImportService $importService,
        RuleRepository $ruleRepository,
        Config $config
    ) {
        $this->templateFactory = $templateFactory;
        $this->importService   = $importService;
        $this->ruleRepository  = $ruleRepository;
        $this->config          = $config;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $templatesPath = dirname(__FILE__) . '/data/template/';
        foreach (scandir($templatesPath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }


            $this->templateFactory->create()->import('Setup/data/template/' . $file);
        }

        $rulesPath = dirname(__FILE__) . '/data/rule/';
        foreach (scandir($rulesPath) as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }

            $this->importService->import(
                $this->ruleRepository->create(),
                'Setup/data/rule/' . $file
            );
        }
    }
}
