<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Plugin\Widget\Controller\Adminhtml\Widget\Instance;

class DeletePlugin
{
    public function beforeGetUrl(\Magento\Widget\Controller\Adminhtml\Widget\Instance\Delete $subject, $route = '', $params = [])
    {
        $back = $subject->getRequest()->getParam('grid');

        if ($back === "amasty_countdown" && $route === "adminhtml/*/") {
            return ['amasty_promo_countdown/instance/', []];
        }

        return [$route, $params];
    }
}
