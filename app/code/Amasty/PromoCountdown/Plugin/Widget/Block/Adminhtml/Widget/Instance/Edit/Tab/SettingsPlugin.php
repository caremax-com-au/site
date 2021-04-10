<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Plugin\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab;

class SettingsPlugin
{
    public function beforeGetFormHtml(\Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Settings $subject)
    {
        $code = $subject->getRequest()->getParam('code');

        if ($code == 'amasty_promo_countdown' && $element = $subject->getForm()->getElement('code')) {
            $element->setReadonly(true)->setValue($code);
        }
    }
}
