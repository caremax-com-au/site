<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Block\Adminhtml\Widgets\Config;

class Color extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    private $elementFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->elementFactory = $elementFactory;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /** @var \Magento\Framework\Data\Form\Element\Text $input */
        $input = $this->elementFactory->create("text", ['data' => $element->getData()]);

        $input->setName($element->getName());
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setData('readonly', 1);
        $input->setClass("widget-option input-text admin__control-text admin-amasty-countdown-field");

        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $value = str_replace('#', '', $input->getData('value'));
        $inverseHex = $value ? '#' . dechex(16777215 - hexdec($value)) : "";

        $html = $input->getElementHtml();
        $html .= "<script>
            require([
                'Amasty_PromoCountdown/js/color'
                ], function (Color) {
                    Color({
                        htmlId:\"" . $input->getHtmlId() . "\",
                        value:\"" . $input->getValue() . "\",
                        inverseHex: \"" . $inverseHex . "\"
                    });
                    })
        </script>";

        $element->setData('after_element_html', $html);
        $element->setValue('');

        return $element;
    }
}
