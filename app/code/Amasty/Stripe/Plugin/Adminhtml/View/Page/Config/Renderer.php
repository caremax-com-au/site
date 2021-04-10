<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Plugin\Adminhtml\View\Page\Config;

use Magento\Framework\View\Page\Config\Renderer as MagentoRenderer;
use Amasty\Stripe\Gateway\Config\Config as ConfigProvider;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\GroupedCollection;

/**
 * Class Renderer
 */
class Renderer
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Repository
     */
    private $assetRepository;

    /**
     * @var GroupedCollection
     */
    private $pageAssets;

    public function __construct(
        ConfigProvider $configProvider,
        Repository $assetRepository,
        GroupedCollection $pageAssets
    ) {
        $this->configProvider = $configProvider;
        $this->assetRepository = $assetRepository;
        $this->pageAssets = $pageAssets;
    }

    /**
     * @param MagentoRenderer $subject
     * @param array $resultGroups
     * @return array
     */
    public function beforeRenderAssets(
        MagentoRenderer $subject,
        $resultGroups = []
    ) {
        if (!$this->configProvider->isActive()) {
            $this->stripeDisable();
        }

        return [$resultGroups];
    }

    private function stripeDisable()
    {
        $file = 'Amasty_Stripe::js/amastyStripeDisabled.js';
        $asset = $this->assetRepository->createAsset($file);
        $this->pageAssets->insert($file, $asset, 'requirejs/require.js');
    }
}
