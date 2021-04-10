<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Block;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context;
use Magento\Framework\View\Element\Template;

class Popup extends Template
{
    /**
     * @var \Amasty\SocialLogin\Model\ConfigData
     */
    private $configData;

    /**
     * @var \Amasty\SocialLogin\Model\Source\ButtonPosition
     */
    private $buttonPosition;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var Context
     */
    private $httpContext;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\SocialLogin\Model\ConfigData $configData,
        Context $httpContext,
        \Amasty\SocialLogin\Model\Source\ButtonPosition $buttonPosition,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configData = $configData;
        $this->buttonPosition = $buttonPosition;
        $this->jsonEncoder = $jsonEncoder;
        $this->httpContext = $httpContext;
    }

    /**
     * @return bool
     */
    public function isSocialsEnabled()
    {
        return $this->configData->getConfigValue('general/enabled')
            && !$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * @return bool
     */
    public function isPopupEnabled()
    {
        return $this->configData->getConfigValue('general/popup_enabled');
    }

    /**
     * @return string
     */
    public function getPositionTitle()
    {
        return $this->configData->getPositionTitle();
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->jsonEncoder->encode([
            'logout_url'    => $this->getUrl('amsociallogin/logout/index'),
            'header_update' => $this->getUrl('amsociallogin/header/update'),
            'reset_pass_url' => $this->getUrl('customer/account/forgotpasswordpost')
        ]);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getChildHtmlAndReplaceIds(string $name): string
    {
        $formHtml = $this->getChildBlock($name)->toHtml();
        $formHtml = str_replace('form-validate', 'am-form-validate', $formHtml);
        $formHtml = str_replace('email_address', 'am-email-address', $formHtml);

        return $formHtml;
    }
}
