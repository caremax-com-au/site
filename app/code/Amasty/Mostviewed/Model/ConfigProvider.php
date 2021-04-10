<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\Mostviewed\Model\OptionSource\DisplayOptions;

class ConfigProvider extends ConfigProviderAbstract
{
    const DISPLAY_OPTIONS_PATH = 'bundle_packs/display_options';
    const CONFIRMATION_TITLE_PATH = 'bundle_packs/confirmation_title';
    const ANALYTIC_ORDER_STATUS_PATH = 'bundle_packs/analytics/order_status';
    const ANALYTIC_PERIOD_PATH = 'bundle_packs/analytics/period';
    const ANALYTIC_BOUGHT_ORDER_STATUS_PATH = 'bundle_packs/analytics/order_status_bought';

    /**
     * @var string
     */
    protected $pathPrefix = 'ammostviewed/';

    public function isShowAllOptions(): bool
    {
        return $this->getValue(self::DISPLAY_OPTIONS_PATH) == DisplayOptions::ALL_OPTIONS;
    }

    public function getConfirmationTitle(): string
    {
        return $this->getValue(self::CONFIRMATION_TITLE_PATH);
    }

    public function getPackAnalyticOrderStatuses(): array
    {
        return $this->getValue(self::ANALYTIC_ORDER_STATUS_PATH)
            ? explode(',', $this->getValue(self::ANALYTIC_ORDER_STATUS_PATH))
            : [];
    }

    public function getPackAnalyticPeriod(): int
    {
        return (int) $this->getValue(self::ANALYTIC_PERIOD_PATH);
    }

    public function getPackAnalyticBoughtOrderStatuses(): array
    {
        return $this->getValue(self::ANALYTIC_BOUGHT_ORDER_STATUS_PATH)
            ? explode(',', $this->getValue(self::ANALYTIC_BOUGHT_ORDER_STATUS_PATH))
            : [];
    }

    /**
     * Check is Ajax Cart module Product Page config.
     * @return bool
     */
    public function isCartEnabledOnProductPage(): bool
    {
        return $this->scopeConfig->isSetFlag('amasty_cart/confirm_popup/use_on_product_page');
    }

    /**
     * Ajax Cart module: Image Display for Configurable Products config.
     * @return bool
     */
    public function isChildImageForConfigurable(): bool
    {
        return $this->scopeConfig->isSetFlag('amasty_cart/confirm_display/configurable_image');
    }
}
