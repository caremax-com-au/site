<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Block\Form\Registration;

use Amasty\SocialLogin\Model\Config\GdprSocialLogin;
use Magento\Framework\View\Element\Template;

class Gdpr extends Template
{
    /**
     * @return array|mixed|string|null
     */
    protected function _toHtml()
    {
        $result = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch(
            'amasty_gdpr_get_checkbox',
            [
                'scope' => GdprSocialLogin::GDPR_SOCIAL_LOGIN__FORM,
                'result' => $result
            ]
        );

        return $result->getData('html') ?: '';
    }
}
