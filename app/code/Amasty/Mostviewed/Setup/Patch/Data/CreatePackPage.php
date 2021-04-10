<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Setup\Patch\Data;

use Amasty\Mostviewed\Helper\Config;
use Exception;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class CreatePackPage implements DataPatchInterface, PatchRevertableInterface
{
    const IDENTIFIER = 'bundles';

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var PageResource
     */
    private $pageResource;

    public function __construct(
        WriterInterface $configWriter,
        PageFactory $pageFactory,
        PageResource $pageResource,
        ReinitableConfigInterface $reinitableConfig
    ) {
        $this->pageFactory = $pageFactory;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
        $this->pageResource = $pageResource;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return CreatePackPage
     */
    public function apply()
    {
        if ($this->isCanApply()) {
            $content = <<<CONTENT
<h2>
    <strong>Searching for special deals? Browse the list below to find the offer you're looking for!</strong>
</h2>
<p></p>
<p>{{widget type="Amasty\Mostviewed\Block\Widget\PackList" columns="3"template="bundle/list.phtml"}}</p>
CONTENT;

            $page = $this->pageFactory->create();
            $page->setTitle('All Bundle Packs Page')
                ->setIdentifier(self::IDENTIFIER)
                ->setData('mageworx_hreflang_identifier', 'en-us')
                ->setData('amasty_hreflang_uuid', 'en-us')
                ->setData('mp_exclude_sitemap', '1')
                ->setIsActive(false)
                ->setPageLayout('1column')
                ->setStores([0])
                ->setContent($content)
                ->save();

            $this->configWriter->save(Config::BUNDLE_PAGE_PATH, self::IDENTIFIER);
            $this->reinitableConfig->reinit();
        }

        return $this;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function revert()
    {
        /** @var Page $page */
        $page = $this->pageFactory->create();
        $page->load(self::IDENTIFIER, PageInterface::IDENTIFIER);
        if ($page->getId()) {
            $this->pageResource->delete($page);
        }
    }

    private function isCanApply(): bool
    {
        /** @var Page $page */
        $page = $this->pageFactory->create();
        $page->load(self::IDENTIFIER, PageInterface::IDENTIFIER);

        return !$page->getId();
    }
}
