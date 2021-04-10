<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Model;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{

    const GENERAL_GROUP = 'general/';

    const ENABLE_FIELD = 'enable';

    protected $pathPrefix = 'amasty_countdown/';

    /**
     * @param string $store
     *
     * @return string
     */
    public function isModuleEnable($store)
    {
        return $this->getValue(self::GENERAL_GROUP . self::ENABLE_FIELD, $store);
    }
}
