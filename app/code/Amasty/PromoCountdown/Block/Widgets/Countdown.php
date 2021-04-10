<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Block\Widgets;

use Amasty\PromoCountdown\Model\ConfigProvider;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Countdown extends Template implements BlockInterface
{
    const API_METHOD = 'rest';
    const API_VERSION = 'V1';
    const API_PATH = 'promo-countdown/service/date/difference';

    const DESIGN_BASE_PATH = 'Amasty_PromoCountdown/js/design/';
    const DESIGN_BASE_COMPONENT = 'uiComponent';

    /**
     * @var string
     * @codingStandardsIgnoreStart
     */
    protected $_template = "Amasty_PromoCountdown::countdown.phtml";
    //@codingStandardsIgnoreEnd

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        Template\Context $context,
        DateTime $dateTime,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->dateTime = $dateTime;
        $this->configProvider = $configProvider;
    }

    public function getTemplate()
    {
        if ($this->configProvider->isModuleEnable($this->_storeManager->getStore()->getId())) {
            return parent::getTemplate();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getDesignComponent()
    {
        $design = $this->getData('design');

        if (in_array($design, \Amasty\PromoCountdown\Model\Config\Design::ANIMATED_DESIGNS)) {
            return self::DESIGN_BASE_PATH . $design;
        }

        return self::DESIGN_BASE_COMPONENT;
    }

    /**
     * @param string $hexColor
     * @param int $percent
     *
     * @return string
     */
    public function luminance($hexColor, $percent)
    {
        $hexColor = array_map('hexdec', str_split(str_pad(str_replace('#', '', $hexColor), 6, '0'), 2));

        foreach ($hexColor as $i => $color) {
            $from = $color;
            $to = 255;

            if ($percent < 0) {
                $from = 0;
                $to = $color;
            }

            $pvalue = ceil(($to - $from) * $percent / 100);
            $hexColor[$i] = str_pad(dechex($color + $pvalue), 2, '0', STR_PAD_LEFT);
        }

        return '#' . implode($hexColor);
    }

    /**
     * @return string
     */
    public function getPostfix()
    {
        return $this->getJsId($this->getStartTime(), $this->getTargetTime(), $this->getData('backgroundColor'));
    }

    /**
     * @return int
     */
    public function getTargetTime()
    {
        return $this->dateTime->gmtTimestamp($this->getData('date_to'));
    }

    /**
     * @return int
     */
    public function getStartTime()
    {
        return $this->dateTime->gmtTimestamp($this->getData('date_from'));
    }

    /**
     * @return string
     */
    public function getServiceUrl()
    {
        $data = [
            self::API_METHOD,
            $this->_storeManager->getStore()->getCode(),
            self::API_VERSION,
            self::API_PATH
        ];

        return $this->getUrl(implode('/', $data));
    }
}
