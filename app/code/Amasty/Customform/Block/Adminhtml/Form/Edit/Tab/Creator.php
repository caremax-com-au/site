<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Block\Adminhtml\Form\Edit\Tab;

use Amasty\Customform\Model\Config\Source\Design;

/**
 * Form page edit form Creator tab
 */
class Creator extends AbstractTab
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    private $factoryElement;

    /**
     * @var Design
     */
    private $design;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        Design $design,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->factoryElement = $factoryElement;
        $this->design = $design;
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Amasty\Customform\Model\Form $model */
        $model = $this->_coreRegistry->registry('amasty_customform_form');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('form_');

        $element = $this->factoryElement->create(
            \Amasty\Customform\Block\Adminhtml\Data\Form\Element\Creator::class,
            [
                'data' => [
                    'name' => 'creator',
                    'label' => '',
                    'title' => __('Form Creator'),
                ]
            ]
        );
        $element->setId('creator')->setLegend(__('Form Creator'));

        $fieldset = $form->addFieldset('content_fieldset', ['legend' => __('Form Design')]);
        $fieldset->addField('design', 'select', [
            'name' => 'design',
            'label' => __('Choose Design'),
            'title' => __('Choose Design'),
            'values' => $this->getDesignList(),
        ]);

        $form->addElement($element);
        $jsonElement = $this->factoryElement->create(
            'hidden',
            [
                'data' => [
                    'id' => 'form_json',
                    'name' => 'form_json'
                ]
            ]
        )->setId('form_json');
        $form->addElement($jsonElement);
        $jsonTitles = $this->factoryElement->create(
            'hidden',
            [
                'data' => [
                    'id' => 'form_title',
                    'name' => 'form_title'
                ]
            ]
        )->setId('form_title');
        $form->addElement($jsonTitles);

        $form->setValues($model->getData());
        $this->setForm($form);

        parent::_prepareForm();
        return $this;
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Form Creator');
    }

    /**
     * @return array
     */
    public function getDesignList(): array
    {
        return $this->design->toOptionArray();
    }
}
