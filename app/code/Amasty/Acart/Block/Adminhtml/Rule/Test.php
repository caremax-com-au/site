<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Block\Adminhtml\Rule;

use Amasty\Acart\Controller\RegistryConstants;

class Test extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Acart\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Acart\Model\ConfigProvider $configProvider,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->configProvider = $configProvider;

        return parent::__construct($context, $data);
    }

    /**
     * @return \Amasty\Acart\Model\Rule
     */
    public function getRule()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_AMASTY_ACART_RULE);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getRuleText()
    {
        return $this->configProvider->getRecipientEmailForTest()
            ? __('Test email(s) sent')
            : __('Email(s) was added to the queue');
    }
}
