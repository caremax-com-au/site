<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Block\Adminhtml\Widgets\Config;

class Textarea extends \Magento\Backend\Block\Template
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
        /** @var \Magento\Framework\Data\Form\Element\Textarea $input */
        $input = $this->elementFactory->create("textarea", ['data' => $element->getData()]);

        $input->setName($element->getName());
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass("widget-option input-text admin__control-text");

        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $html = $input->getElementHtml();
        $element->setData('after_element_html', $html);
        $element->setValue('');

        return $element;
    }
}
