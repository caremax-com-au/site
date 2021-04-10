<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Block\Adminhtml\Form\Edit\Tab;

use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Amasty\Customform\Api\Data\FormInterface;

class Main extends AbstractTab
{
    const SYSTEM_CONFIG_VALUE = 2;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $systemStore;

    /**
     * @var \Amasty\Customform\Model\Config\Source\CustomerGroup
     */
    private $groupSourceFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\Email\Template
     */
    private $emailTemplateSource;

    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    private $yesNoFactory;

    /**
     * @var \Amasty\Customform\Model\Config\Source\AutoReplyTemplate
     */
    private $autoReplyTemplateSource;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    private $depFieldFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Amasty\Customform\Model\Config\Source\CustomerGroup $groupSourceFactory,
        \Magento\Config\Model\Config\Source\Email\Template $emailTemplateSource,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesNoFactory,
        \Amasty\Customform\Model\Config\Source\AutoReplyTemplate $autoReplyTemplateSource,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $depFieldFactory,
        ObjectFactory $objectFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->systemStore = $systemStore;
        $this->groupSourceFactory = $groupSourceFactory;
        $this->emailTemplateSource = $emailTemplateSource;
        $this->yesNoFactory = $yesNoFactory;
        $this->autoReplyTemplateSource = $autoReplyTemplateSource;
        $this->objectFactory = $objectFactory;
        $this->depFieldFactory = $depFieldFactory;
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

        $this->addInfoFieldSet($form, $model);
        $notificationData = $this->addNotificationsFieldSet($form);
        $this->addResponseFieldSet($form, $model);
        $this->addResponderFieldSet($form);

        /* default values */
        if (!$model->getId()) {
            $model->setData('status', \Amasty\Customform\Model\Form::STATUS_ENABLED);
            $model->setData('customer_group', $this->getGroupValues());
            $model->setData('store_id', '0');
        }

        /** @var Dependence $dependence */
        $dependence = $this->getLayout()->createBlock(Dependence::class);
        $depValue = $this->depFieldFactory->create(
            ['fieldData' => ['value' => '1,2', 'separator' => ',']]
        );
        $this->addDependencies(
            $dependence,
            $notificationData->getData('sendNotification'),
            $notificationData->getData('sendTo'),
            $depValue
        );
        $this->addDependencies(
            $dependence,
            $notificationData->getData('sendNotification'),
            $notificationData->getData('emailTemplate'),
            $depValue
        );
        $this->setChild('form_after', $dependence);

        $form->setValues($model->getData());
        $this->setForm($form);

        parent::_prepareForm();

        return $this;
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @param \Amasty\Customform\Model\Form $model
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function addInfoFieldSet(\Magento\Framework\Data\Form &$form, \Amasty\Customform\Model\Form &$model)
    {
        $infoFieldSet = $form->addFieldset('base_fieldset', ['legend' => __('Form Information')]);

        if ($model->getId()) {
            $infoFieldSet->addField('form_id', 'hidden', ['name' => 'form_id']);
        }

        $infoFieldSet->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
            ]
        );
        $infoFieldSet->addField(
            'code',
            'text',
            [
                'name' => 'code',
                'label' => __('Code'),
                'title' => __('Code'),
                'required' => true,
            ]
        );

        $infoFieldSet->addField(
            'success_url',
            'text',
            [
                'name' => 'success_url',
                'label' => __('Success Url'),
                'title' => __('Success Url'),
                'note' => __(
                    'Leave empty for redirect to homepage. Set "/" to use AJAX without page reload.
                     Form with file upload requires page reload anyway.'
                ),
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $infoFieldSet->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'store_id[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->systemStore->getStoreValuesForForm(false, true),
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $infoFieldSet->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id[]', 'values' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $infoFieldSet->addField('customer_group', 'multiselect', [
            'name' => 'customer_group[]',
            'label' => ('Customer Groups'),
            'title' => ('Customer Groups'),
            'values' => $this->groupSourceFactory->toOptionArray(),
        ]);

        $infoFieldSet->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'values' => $model->getAvailableStatuses()
            ]
        );

        $yesNo = $this->yesNoFactory->create()->toOptionArray();
        $infoFieldSet->addField(
            FormInterface::SURVEY_MODE_ENABLE,
            'select',
            [
                'label' => __('Use as Survey'),
                'title' => __('Use as Survey'),
                'name' => FormInterface::SURVEY_MODE_ENABLE,
                'values' => $yesNo,
                'note' => __('When enabled, customer will be able to submit the form only once.')
            ]
        );

        $infoFieldSet->addField(
            'save_referer_url',
            'select',
            [
                'label' => __('Save Referrer Url'),
                'title' => __('Save Referrer Url'),
                'name' => 'save_referer_url',
                'values' => $yesNo,
            ]
        );
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @return \Magento\Framework\DataObject
     */
    private function addNotificationsFieldSet(\Magento\Framework\Data\Form &$form)
    {
        $notificationsFieldSet = $form->addFieldset(
            'notifications_fieldset',
            ['legend' => __('Email Notifications')]
        );

        $sendNotification = $notificationsFieldSet->addField(
            'send_notification',
            'select',
            [
                'label' => __('Send Notification to Email'),
                'title' => __('Send Notification to Email'),
                'name' => 'send_notification',
                'values' => $this->getEnabledOptions(),
            ]
        );

        $sendTo = $notificationsFieldSet->addField(
            'send_to',
            'text',
            [
                'name' => 'send_to',
                'label' => __('Recipients Email'),
                'title' => __('Recipients Email'),
                'note' => __('Comma separated Emails, no spaces.')
            ]
        );

        $emailTemplates = $this->emailTemplateSource->setPath('amasty/customform/email/template')->toOptionArray();
        $emailTemplate = $notificationsFieldSet->addField(
            'email_template',
            'select',
            [
                'label' => __('Email Template'),
                'title' => __('Email Template'),
                'name' => 'email_template',
                'values' => $emailTemplates
            ]
        );

        $data = [
            'sendNotification' => $sendNotification,
            'sendTo' => $sendTo,
            'emailTemplate' => $emailTemplate
        ];

        return $this->objectFactory->create(['data' => $data]);
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @param \Amasty\Customform\Model\Form $model
     */
    private function addResponseFieldSet(\Magento\Framework\Data\Form &$form, \Amasty\Customform\Model\Form &$model)
    {
        $responseFieldSet = $form->addFieldset('response_fieldset', ['legend' => __('Answer the Submitted Form')]);
        $responseFieldSet->addField(
            FormInterface::EMAIL_FIELD,
            'select',
            [
                'name' => FormInterface::EMAIL_FIELD,
                'label' => __('Email Address Field'),
                'title' => __('Email Address Field'),
                'note' => __("Choose a field to be used as e-mail address for manual and auto response reply to the
                    customer. Note: Place 'Text Input' field in the form and 'Save' the form to be able to select the
                    field here. If empty, email address specified in the customer account will be used and reply to
                    guests will not be sent."),
                'values' => $this->getEmailFieldValues($model)
            ]
        );
        $responseFieldSet->addField(
            FormInterface::EMAIL_FIELD_HIDE,
            'select',
            [
                'label' => __('Hide Email Address Field'),
                'title' => __('Hide email Address Field'),
                'note' => __("Set 'Yes' to automatically hide Email Address Field in the form for logged in customer
                    and use email address specified in the customer account."),
                'name' => FormInterface::EMAIL_FIELD_HIDE,
                'values' => $this->yesNoFactory->create()->toOptionArray(),
            ]
        );
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     */
    private function addResponderFieldSet(\Magento\Framework\Data\Form &$form)
    {
        $responderFieldSet = $form->addFieldset('autoresponder_fieldset', ['legend' => __('Autoresponder')]);

        $responderFieldSet->addField(
            FormInterface::AUTO_REPLY_ENABLE,
            'select',
            [
                'label' => __('Enable Auto Response'),
                'title' => __('Enable Auto Response'),
                'note' => __('Please note. "Email Address Field" should be selected for detecting recipient email.'),
                'name' => FormInterface::AUTO_REPLY_ENABLE,
                'values' => $this->getEnabledOptions(),
            ]
        );
        $responderFieldSet->addField(
            FormInterface::AUTO_REPLY_TEMPLATE,
            'select',
            [
                'name' => FormInterface::AUTO_REPLY_TEMPLATE,
                'label' => __('Email Template'),
                'title' => __('Email Template'),
                'values' => $this->getAutoReplyEmailOptions()
            ]
        );
    }

    /**
     * @return array
     */
    private function getEnabledOptions()
    {
        $enabledOptions = $this->yesNoFactory->create()->toOptionArray();

        return $this->addSystemConfigValue($enabledOptions);
    }

    /**
     * @return array
     */
    private function getAutoReplyEmailOptions()
    {
        $autoReplyEmailOptions = $this->autoReplyTemplateSource->toOptionArray();

        return $this->addSystemConfigValue($autoReplyEmailOptions);
    }

    /**
     * @param array $values
     * @return array
     */
    private function addSystemConfigValue($values = [])
    {
        array_unshift(
            $values,
            ['label' => __('Use System Config Value'), 'value' => self::SYSTEM_CONFIG_VALUE]
        );

        return $values;
    }

    /**
     * @return array
     */
    private function getGroupValues()
    {
        $groupValues = $this->groupSourceFactory->toOptionArray();

        return array_column($groupValues, 'value');
    }

    /**
     * @param \Amasty\Customform\Model\Form $model
     * @return array
     */
    private function getEmailFieldValues(\Amasty\Customform\Model\Form $model)
    {
        $values = [['label' => __('-- Please save this form first --'), 'value' => '']];
        if ($model->getId()) {
            $values = [['label' => __('-- Please select --'), 'value' => '']];

            foreach ($model->getDecodedFormJson() as $page) {
                // support for old versions of forms
                if (isset($page['type'])) {
                    $values = $this->dataProcessing($values, $page);
                } else {
                    foreach ($page as $field) {
                        $values = $this->dataProcessing($values, $field);
                    }
                }
            }
        }

        return $values;
    }

    /**
     * @return array
     */
    private function dataProcessing($values, $data)
    {
        if ($data['type'] === 'textinput') {
            $label = isset($data['label']) ? $data['label'] : '';
            $values[] = ['label' => $label, 'value' => $data['name']];
        }

        return $values;
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Form Information');
    }
}
