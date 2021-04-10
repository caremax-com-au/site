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



namespace Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Feed\Api\Data\RuleInterface;
use Mirasvit\Feed\Repository\RuleRepository;
use Mirasvit\Feed\Service\Rule\ToStringService;

class Rule extends Form
{
    private $ruleRepository;

    private $toStringService;

    private $formFactory;

    private $registry;

    public function __construct(
        RuleRepository $ruleRepository,
        ToStringService $toStringService,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->ruleRepository  = $ruleRepository;
        $this->toStringService = $toStringService;
        $this->formFactory     = $formFactory;
        $this->registry        = $registry;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('current_model');
        $form  = $this->formFactory->create();
        $form->setFieldNameSuffix('feed');
        $this->setForm($form);

        $productFieldset = $form->addFieldset('feed_tab_rule_product', ['legend' => __('Product Filters')]);

        foreach ($this->ruleRepository->getCollection() as $rule) {
            $this->addRuleToFieldset($rule, $productFieldset, $model);
        }

        return parent::_prepareForm();
    }

    /**
     * Add rule output to fieldset
     *
     * @param RuleInterface                                 $rule
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param \Mirasvit\Feed\Model\Feed                     $feed
     *
     * @return $this
     */
    protected function addRuleToFieldset(RuleInterface $rule, $fieldset, $feed)
    {
        $fieldset->addField('rule' . $rule->getId(), 'checkbox', [
            'label'    => $rule->getName(),
            'name'     => 'rule_ids[' . $rule->getId() . ']',
            'checked'  => in_array($rule->getId(), $feed->getRuleIds()),
            'required' => false,
            'note'     => $this->toStringService->toString($rule),
        ]);

        return $this;
    }
}
