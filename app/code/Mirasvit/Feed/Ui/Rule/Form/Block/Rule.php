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


declare(strict_types=1);

namespace Mirasvit\Feed\Ui\Rule\Form\Block;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as FieldsetRenderer;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Rule\Block\Conditions;
use Mirasvit\Feed\Controller\Registry;
use Mirasvit\Feed\Repository\RuleRepository;

class Rule extends Form implements TabInterface
{
    protected $_nameInLayout = 'conditions_serialized';

    private   $ruleRepository;

    private   $fieldsetRenderer;

    private   $conditions;

    private   $formFactory;

    private   $registry;

    private   $context;

    public function __construct(
        RuleRepository $ruleRepository,
        Conditions $conditions,
        FieldsetRenderer $fieldsetRenderer,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->ruleRepository   = $ruleRepository;
        $this->fieldsetRenderer = $fieldsetRenderer;
        $this->conditions       = $conditions;
        $this->formFactory      = $formFactory;
        $this->registry         = $registry;
        $this->context          = $context;

        parent::__construct($context);
    }

    public function getTabLabel()
    {
        return __('Conditions');
    }

    public function getTabTitle()
    {
        return __('Conditions');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $formName = \Mirasvit\Feed\Model\Rule\Rule::FORM_NAME;

        $rule = $this->registry->getRule();

        $ruleInstance = $this->ruleRepository->getRuleInstance($rule);

        $form = $this->formFactory->create();
        $form->setData('html_id_prefix', 'rule_');

        $fieldsetName = 'conditions_fieldset';

        $renderer = $this->fieldsetRenderer
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setData('new_child_url', $this->getUrl('mst_feed/rule/newConditionHtml', [
                'form'      => 'rule_' . $fieldsetName,
                'form_name' => $formName,
            ]));

        $fieldset = $form->addFieldset($fieldsetName, [
            'legend' => __('Filter Conditions'),
        ])->setRenderer($renderer);

        $ruleInstance->getConditions()
            ->setFormName($formName);

        $conditionsField = $fieldset->addField('conditions', 'text', [
            'name'           => 'conditions',
            'required'       => true,
            'data-form-part' => $formName,
        ]);

        $conditionsField->setRule($ruleInstance)
            ->setRenderer($this->conditions)
            ->setFormName($formName);

        $form->setValues($rule->getData());
        $this->setConditionFormName($ruleInstance->getConditions(), $formName);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param object $conditions
     * @param string $formName
     *
     * @return void
     */
    private function setConditionFormName($conditions, string $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }
}
