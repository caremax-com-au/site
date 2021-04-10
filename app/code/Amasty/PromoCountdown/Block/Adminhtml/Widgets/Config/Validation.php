<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Block\Adminhtml\Widgets\Config;

class Validation extends \Magento\Backend\Block\Template
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
        $input = $this->elementFactory->create("hidden", ['data' => $element->getData()]);
        $input->setForm($element->getForm());
        $html = $input->getElementHtml();
        $html .= "<script>
            require([
                'jquery',
                'Amasty_PromoCountdown/js/validation'
                ], function ($) {
                    if ($('#widget_options_form').length) {
                        $('body').trigger('contentUpdated');
                        $('#widget_options_form').applyBindings();
                    }
                })
        </script>";

        $element->setData('after_element_html', $html);

        return $element;
    }
}
