<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

class GdprSocialLogin implements OptionSourceInterface
{
    const GDPR_SOCIAL_LOGIN__FORM = 'amsociallogin_popup_form';

    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => [
                    ['value' => self::GDPR_SOCIAL_LOGIN__FORM, 'label' => __('Popup Registration Form')]
                ],
                'label' => __('Social Login')
            ]
        ];
    }
}
