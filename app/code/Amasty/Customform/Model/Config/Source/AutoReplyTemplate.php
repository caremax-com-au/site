<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\Config\Source;

class AutoReplyTemplate extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    const DEFAULT_TEMPLATE_CODES = [
        'amasty_customform_autoresponder_template',
        'amasty_customform_autoresponder_with_submited_fields_template'
    ];

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $emailConfig;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    private $templatesFactory;

    /**
     * @var array
     */
    private $templateCodes = [];

    public function __construct(
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $additionalTemplateCodes = [],
        array $data = []
    ) {
        parent::__construct($data);
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        $this->templateCodes = array_merge(self::DEFAULT_TEMPLATE_CODES, $additionalTemplateCodes);
    }

    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var $collection \Magento\Email\Model\ResourceModel\Template\Collection */
        $collection = $this->templatesFactory->create();
        $collection->addFieldToFilter('orig_template_code', ['in' => self::DEFAULT_TEMPLATE_CODES]);
        $options = $collection->toOptionArray();
        $options = array_merge($this->getTemplateList(), $options);

        return $options;
    }

    private function getTemplateList(): array
    {
        return array_map(function (string $code) {
            $templateLabel = $this->emailConfig->getTemplateLabel($code);
            $labelString = '%1';
            $labelString .= in_array($code, self::DEFAULT_TEMPLATE_CODES) ? ' (Default)' : '';
            $templateLabel = __($labelString, $templateLabel);

            return ['value' => $code, 'label' => $templateLabel];
        }, $this->templateCodes);
    }
}
