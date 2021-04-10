<?php

namespace Olegnax\Athlete2\Block\Adminhtml;

use Olegnax\Athlete2\Block\Template;

class Notice extends Template
{

    protected function _toHtml()
    {
        if ('athlete2_license' !== $this->getRequest()->getParam('section')) {
            $license = $this->getHelper()->get();
            $notice = '';
            if (empty($license)) {
                $notice = '<div class="ox-license-status ox-notice-license status-disable"><span class="icon"></span><div class="inner"><h2 class="ox-license-status__title">' . __('Athlete2 Theme ') . '<span class="undelined">' . __('Not Activated!') . '</span><a href="' . $this->getLicenseUrl() . '">' . __('Click here to Activate') . '</a></h2></div></div>';
            }
            return $notice;
        }

        return '';
    }

    protected function getLicenseUrl()
    {
        return $this->getUrl('*/*/*', ['_current' => true, 'section' => 'athlete2_license']);
    }

}
