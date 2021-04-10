<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Plugin\Widget\Block\Adminhtml\Widget\Instance;

class EditPlugin
{
    public function beforeGetUrl(
        \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit $subject,
        $route = '',
        $params = []
    ) {
        $back = $subject->getRequest()->getParam('grid');

        if ($back === "amasty_countdown") {
            switch ($route) {
                case '*/*/':
                    $route = 'amasty_promo_countdown/instance/';
                    break;
                case '*/*/delete':
                    $params['grid'] = 'amasty_countdown';
                    break;
            }

        }

        return [$route, $params];
    }
}
