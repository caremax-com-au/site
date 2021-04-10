<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Block\Form;

class Registration extends \Magento\Customer\Block\Form\Register
{
    /**
     * @var array
     */
    private $formRenderers = [
        'text',
        'textarea',
        'multiline',
        'date',
        'select',
        'multiselect',
        'boolean',
        'file',
        'image'
    ];

    /**
     * @return Registration|\Magento\Customer\Block\Form\Register|\Magento\Framework\View\Element\AbstractBlock
     */
    public function _prepareLayout()
    {
        $parent = $this->getParentBlock();

        return $parent ? $parent->_prepareLayout() : $this;
    }

    public function toHtml()
    {
        if ($this->_moduleManager->isEnabled('Magento_CustomerCustomAttributes')) {
            $this->changePopupTemplate();
        }

        return parent::toHtml();
    }

    private function changePopupTemplate()
    {
        $this->setTemplate('Magento_CustomerCustomAttributes::/customer/form/register.phtml');
        $formBlock = $this->addChild(
            'customer_form_user_attributes',
            \Magento\CustomerCustomAttributes\Block\Form::class
        );

        if (!$this->getLayout()->getBlock('customer_form_template')) {
            $customerFormTemplate = $this->getLayout()
                ->createBlock(\Magento\Framework\View\Element\Template::class, 'customer_form_template');

            foreach ($this->formRenderers as $formRenderer) {
                $customerFormTemplate->addChild(
                    $formRenderer,
                    'Magento\\CustomAttributeManagement\\Block\\Form\\Renderer\\' . ucfirst($formRenderer)
                )->setTemplate('Magento_CustomerCustomAttributes::form/renderer/' . $formRenderer . '.phtml');
            }
        }

        $formBlock->setEntityType('customer')
            ->setEntityModelClass(\Magento\Customer\Model\Customer::class)
            ->setFormCode('customer_account_create')
            ->setShowContainer(false)
            ->setBlockId('customer_form_user_attributes')
            ->setTemplate('Magento_CustomerCustomAttributes::form/userattributes.phtml');
    }
}
