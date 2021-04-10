<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.30
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Block\Adminhtml\Import\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Feed\Model\Config;
use Mirasvit\Feed\Model\Config\Source\Dynamic\Attribute as SourceAttribute;
use Mirasvit\Feed\Model\Config\Source\Dynamic\Category as SourceCategory;
use Mirasvit\Feed\Model\Config\Source\Dynamic\Variable as SourceVariable;
use Mirasvit\Feed\Model\Config\Source\ImportEntities;
use Mirasvit\Feed\Model\Config\Source\Rule as SourceRule;
use Mirasvit\Feed\Model\Config\Source\Template as SourceTemplate;

/** mp comment start **/
/** mp comment end **/

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Import extends Form
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ImportEntities
     */
    private $importEntities;

    /**
     * @var SourceTemplate
     */
    private $sourceTemplate;

    /**
     * @var SourceRule
     */
    private $sourceRule;

    /**
     * @var SourceAttribute
     */
    private $sourceAttribute;

    /**
     * @var SourceCategory
     */
    private $sourceCategory;

    /**
     * @var SourceVariable
     */
    private $sourceVariable;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param Context $context
     * @param Config $config
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param ImportEntities $importEntities
     * @param SourceTemplate $sourceTemplate
     * @param SourceRule $sourceRule
     * @param SourceAttribute $sourceAttribute
     * @param SourceVariable $sourceVariable
     * @param SourceCategory $sourceCategory
     */
    public function __construct(
        Context $context,
        Config $config,
        FormFactory $formFactory,
        Registry $registry,
        ImportEntities $importEntities,
        SourceTemplate $sourceTemplate,
        SourceRule $sourceRule,
        SourceAttribute $sourceAttribute,
        /** mp comment start **/
        SourceVariable $sourceVariable,
        /** mp comment end **/
        SourceCategory $sourceCategory
    ) {
        $this->config          = $config;
        $this->formFactory     = $formFactory;
        $this->registry        = $registry;
        $this->importEntities  = $importEntities;
        $this->sourceTemplate  = $sourceTemplate;
        $this->sourceRule      = $sourceRule;
        $this->sourceAttribute = $sourceAttribute;
        $this->sourceCategory  = $sourceCategory;
        /** mp comment start **/
        $this->sourceVariable = $sourceVariable;
        /** mp comment end **/

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create();
        $this->setForm($form);

        $fieldSet = $form->addFieldset(
            'import_fieldset',
            [
                'legend' => __('Select Data to Import'),
            ]
        );

        $sourceImport = $fieldSet->addField(
            'import_data',
            'select',
            [
                'name'   => 'import_data',
                'label'  => __('Select Data to Import'),
                'values' => $this->importEntities->toOptionArray(),
            ]
        );

        $templates = $fieldSet->addField(
            'import_template',
            'multiselect',
            [
                'label'  => __('Templates'),
                'title'  => __('Templates'),
                'name'   => 'template',
                'values' => $this->sourceTemplate->toOptionArray(true),
                'note'   => __('Templates import path: %1', $this->config->printPath($this->config->getTemplatePath())),
            ]
        );

        $rules = $fieldSet->addField(
            'import_rule',
            'multiselect',
            [
                'label'  => __('Filters'),
                'title'  => __('Filters'),
                'name'   => 'rule',
                'values' => $this->sourceRule->toOptionArray(true),
                'note'   => __('Filters import path: %1', $this->config->printPath(($this->config->getRulePath()))),
            ]
        );

        $dynamicAttributes = $fieldSet->addField(
            'import_dynamic_attribute',
            'multiselect',
            [
                'label'  => __('Dynamic Attributes'),
                'title'  => __('Dynamic Attributes'),
                'name'   => 'dynamic_attribute',
                'values' => $this->sourceAttribute->toOptionArray(true),
                'note'   => __('Dynamic Attributes import path: %1', $this->config->printPath($this->config->getDynamicAttributePath())),
            ]
        );

        $dynamicCategories = $fieldSet->addField(
            'import_dynamic_category',
            'multiselect',
            [
                'label'  => __('Dynamic Categories'),
                'title'  => __('Dynamic Categories'),
                'name'   => 'dynamic_category',
                'values' => $this->sourceCategory->toOptionArray(true),
                'note'   => __('Dynamic Categories import path: %1', $this->config->printPath($this->config->getDynamicCategoryPath())),
            ]
        );

        $dynamicVariables = $fieldSet->addField(
            'import_dynamic_variable',
            'multiselect',
            [
                'label'  => __('Dynamic Variables'),
                'title'  => __('Dynamic Variables'),
                'name'   => 'dynamic_variable',
                /** mp comment start **/
                'values' => $this->sourceVariable->toOptionArray(true),
                'note'   => __('Dynamic Variables import path: %1', $this->config->printPath($this->config->getDynamicVariablePath())),
                /** mp comment end **/
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                ->addFieldMap($sourceImport->getHtmlId(), $sourceImport->getName())
                ->addFieldMap($templates->getHtmlId(), $templates->getName())
                ->addFieldMap($rules->getHtmlId(), $rules->getName())
                ->addFieldMap($dynamicAttributes->getHtmlId(), $dynamicAttributes->getName())
                ->addFieldMap($dynamicCategories->getHtmlId(), $dynamicCategories->getName())
                ->addFieldMap($dynamicVariables->getHtmlId(), $dynamicVariables->getName())
                ->addFieldDependence($templates->getName(), $sourceImport->getName(), 'template')
                ->addFieldDependence($rules->getName(), $sourceImport->getName(), 'rule')
                ->addFieldDependence($dynamicAttributes->getName(), $sourceImport->getName(), 'dynamic_attribute')
                ->addFieldDependence($dynamicCategories->getName(), $sourceImport->getName(), 'dynamic_category')
                ->addFieldDependence($dynamicVariables->getName(), $sourceImport->getName(), 'dynamic_variable')
        );

        $importUrl = $this->getUrl('*/*/importAction');
        $button    = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'label'   => 'Import Data',
            'class'   => 'secondary',
            'onclick' => "require('uiRegistry').get('import_processor').process('$importUrl')",
        ]);

        $fieldSet->addField('import_button', 'note', [
            'name' => 'import_button',
            'text' => $button->toHtml(),
        ]);

        return parent::_prepareForm();
    }
}
