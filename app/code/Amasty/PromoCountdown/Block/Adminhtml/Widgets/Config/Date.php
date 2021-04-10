<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Block\Adminhtml\Widgets\Config;

/**
 * Class Date.
 * Can be used as date field in widget configuration.
 * Example Amasty/PromoCountdown/etc/widget.xml
 *
 * Option `date_format` is required.
 * It's ICU date format. @see http://userguide.icu-project.org/formatparse/datetime
 * Another not required options:
 *  time_format - jQuery time format.
 *  min_date (max_date) - minimal (maximal) date value.
 *      It should  fit the `date_format`.
 *      Can take `current` value.
 *  current_date_format - PHP date format.
 *      It's required when `min_date` or `max_date` set to `current` value.
 */
class Date extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    private $elementFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->elementFactory = $elementFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /** @var \Magento\Framework\Data\Form\Element\Date $input */
        $input = $this->elementFactory->create("date", ['data' => $element->getData()]);

        $input->setDateFormat($this->getDateFormat());
        $input->setTimeFormat($this->getTimeFormat());
        $input->setName($element->getName());
        $input->setId($element->getId());
        $input->setData('readonly', 1);
        $input->setForm($element->getForm());
        $input->setClass("widget-option input-text admin__control-text admin-amasty-countdown-field");

        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $input->addClass($this->getAdditionalClasses());
        $input->setMinDate($this->getMinDate());
        $input->setMaxDate($this->getMaxDate());

        if ($this->getMinDate() == 'current') {
            $input->setMinDate($this->dateTime->date($this->getCurrentDateFormat()));
        }

        if ($this->getMaxDate() == 'current') {
            $input->getMaxDate($this->dateTime->date($this->getCurrentDateFormat()));
        }

        $element->setData('after_element_html', $input->getElementHtml());
        $element->setValue('');

        return $element;
    }
}
