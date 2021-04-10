<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Block\Adminhtml\System\Config;

use Amasty\SocialLogin\Model\Source\Shape as Shape;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Information extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    const SECONDS_IN_DAY = 86400;
    const DAYS = 30;

    /**
     * @var string
     */
    private $userGuide = 'https://amasty.com/docs/doku.php?id=magento_2:social_login';

    /**
     * @var string
     */
    private $content;

    /**
     * @var \Amasty\SocialLogin\Model\ConfigData
     */
    private $configData;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Amasty\SocialLogin\Model\ConfigData $configData,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->configData = $configData;
        $this->moduleManager = $moduleManager;
        $this->date = $date;
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $this->setContent(__('Please update Amasty Base module. Re-upload it and replace all the files.'));

        $this->_eventManager->dispatch(
            'amasty_base_add_information_content',
            ['block' => $this]
        );

        $html .= $this->getContent();
        $html .= $this->_getFooterHtml($element);

        $html = str_replace(
            'amasty_information]" type="hidden" value="0"',
            'amasty_information]" type="hidden" value="1"',
            $html
        );
        $html = preg_replace('(onclick=\"Fieldset.toggleCollapse.*?\")', '', $html);

        return $html;
    }

    /**
     * @return array|string
     */
    public function getAdditionalModuleContent()
    {
        if ($this->configData->getButtonShape() != Shape::RECTANGULAR
            && $this->configData->getConfigValue('apple/enabled')
        ) {
            $result[] = [
                'type' => 'message-notice',
                'text' => __('Please kindly keep in mind that Apple guidelines require rectangular shape of button for'
                . 'sign-in. We strongly recommend to make the appropriate changes in Login Buttons Shape setting.')
            ];
        }

        $expirationDate = $this->configData->getConfigValue('apple/secret_exp_date');

        if ($this->isExpDateComing($expirationDate)) {
            $result[] = [
                'type' => 'message-notice',
                'text' => $this->getExpirationMessage($expirationDate)
            ];
        }

        if (!class_exists(\Hybridauth\Hybridauth::class)) {
            $result[] = [
                'type' => 'message-error',
                'text' => __('Additional Social Login package is not installed or need to be updated. '
                    . 'Please, run the following command in the SSH: composer require hybridauth/hybridauth:~3.3')
            ];
        }

        return $result ?? '';
    }

    /**
     * @param $expirationDate
     * @return \Magento\Framework\Phrase
     */
    private function getExpirationMessage($expirationDate)
    {
        $days = (round(($expirationDate - $this->date->gmtTimestamp()) / self::SECONDS_IN_DAY)) + self::DAYS;
        if ($days < 0) {
            $message = __('The Key used for Apple ID authorization has expired. Please timely update all '
                . 'necessary API credentials to guarantee proper further functioning.');
        } else {
            $message = __(
                'The Key used for Apple ID authorization expires %1. Please timely update all '
                . 'necessary API credentials to guarantee proper further functioning.',
                $days == 0 ? 'today' : sprintf('in %s day(s)', $days)
            );
        }

        return $message;
    }

    /**
     * @param int $expirationDate
     * @return bool
     */
    private function isExpDateComing($expirationDate)
    {
        return $this->moduleManager->isEnabled('Amasty_SocialLoginAppleId')
            && $this->configData->getConfigValue('apple/enabled')
            && $expirationDate
            && $expirationDate < $this->date->gmtTimestamp();
    }

    /**
     * @return string
     */
    public function getUserGuide()
    {
        return $this->userGuide;
    }

    /**
     * @param string $userGuide
     */
    public function setUserGuide($userGuide)
    {
        $this->userGuide = $userGuide;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
