<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


declare(strict_types=1);

namespace Amasty\SeoToolKit\Plugin\Catalog\Block\Category;

use Amasty\SeoToolKit\Helper\Config;
use Amasty\SeoToolKit\Plugin\Framework\View\Page\Title;
use Magento\Catalog\Block\Category\View;
use Magento\Catalog\Model\Product\ProductList\Toolbar;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\PropertyGroup;

class ViewPlugin
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Config $config,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->context = $context;
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    public function afterSetLayout(View $subject, View $result): void
    {
        if (!$this->config->isViewAllCanonicalEnabled()) {
            return;
        }

        $pageConfig = $this->context->getPageConfig();
        $assetCollection = $pageConfig->getAssetCollection();
        $canonicalAsset = $assetCollection->getGroupByContentType('canonical');
        if ($canonicalAsset) {
            $page = $subject->getRequest()->getParam('p');
            if ($page && $page > 1) {
                $canonicals = $canonicalAsset->getAll();
                $url = $this->getNewCanonicalUrl($canonicals);
                $this->removeOldCanonicals($assetCollection, $canonicals);

                $pageConfig->addRemotePageAsset(
                    $url,
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
            }
        }
    }

    private function removeOldCanonicals(GroupedCollection $assetCollection, array $canonicals): void
    {
        foreach ($canonicals as $canonicalUrl => $value) {
            $assetCollection->remove($canonicalUrl);
        }
    }

    private function getNewCanonicalUrl(array $canonicals): string
    {
        $oldCanonical = array_key_first($canonicals);
        if (strpos($oldCanonical, Toolbar::LIMIT_PARAM_NAME) !== false) {
            return $oldCanonical;
        }

        // @codingStandardsIgnoreLine
        $parsedUrl = parse_url($oldCanonical);
        $allProductsParam = sprintf('%s=%s', Toolbar::LIMIT_PARAM_NAME, Title::ALL_PRODUCTS_PARAM);
        if (isset($parsedUrl['query']) && $parsedUrl['query']) {
            $parsedUrl['query'] .= '&' . $allProductsParam;
        } else {
            $parsedUrl['query'] = $allProductsParam;
        }

        return $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'] . '?' . $parsedUrl['query'];
    }
}
