<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class RedirectUrl extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Amasty\SocialLogin\Model\SocialData
     */
    private $socialData;

    public function __construct(
        Context $context,
        \Amasty\SocialLogin\Model\SocialData $socialData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->socialData = $socialData;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $elementId   = explode('_', $element->getHtmlId());
        $redirectUrl = $this->socialData->getRedirectUrl($elementId[1]);

        return $this->getFieldTemplate($element, $redirectUrl);
    }

    /**
     * @param $element
     * @param $redirectUrl
     * @return string
     */
    private function getFieldTemplate($element, $redirectUrl)
    {
        $html = '<input style="opacity:1;" readonly id="%s" class="input-text admin__control-text"
                        value="%s" onclick="this.select()" type="text">';

        return sprintf($html, $element->getHtmlId(), $redirectUrl);
    }
}
