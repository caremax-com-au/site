<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Block\Widget\Form\Renderer;

use Magento\Backend\Block\Template\Context;
use Amasty\Customform\Model\Config\Source\DateFormat;

class Creator extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
{
    protected $_template = 'Amasty_Customform::widget/form/renderer/fieldset.phtml';

    private $elementTypeConnection = [
      'textinput'   => 'input',
      'textarea'    => 'input',
      'number'      => 'input',
      'googlemap'   => 'input',
      'date'        => 'select',
      'time'        => 'select',
      'datetime'    => 'select',
      'file'        => 'select',
      'dropdown'    => 'options',
      'listbox'     => 'options',
      'checkbox'    => 'options',
      'checkboxtwo' => 'options',
      'radio'       => 'options',
      'radiotwo'    => 'options',
      'rating'      => 'other',
      'country'     => 'other',
      'address'     => 'other',
      'text'        => 'other',
      'hone'        => 'other',
      'htwo'        => 'other',
      'hthree'      => 'other'
    ];

    private $types = [
        'input'     => 'Input',
        'select'    => 'Select',
        'options'   => 'Options',
        'other'     => 'Advanced'
    ];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Amasty\Customform\Helper\Messages
     */
    private $messagesHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\Customform\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Amasty\Customform\Helper\Messages $messagesHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Customform\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->objectManager = $objectManager;
        $this->messagesHelper = $messagesHelper;
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * @return string
     */
    public function getFormJson()
    {
        /** @var \Amasty\Customform\Model\Form $model */
        $model = $this->registry->registry('amasty_customform_form');
        return $model->getFormJson() ?: '{}';
    }

    /**
     * @return string[]
     */
    public function getElementTypes()
    {
        return $this->types;
    }

    /**
     * @param $type
     *
     * @return array
     */
    public function getElementsByType($type)
    {
        return array_keys($this->elementTypeConnection, $type);
    }

    /**
     * @return string
     */
    public function getFrmbFieldsJson()
    {
        $result = [];
        foreach ($this->elementTypeConnection as $key => $type) {
            $element = $this->_createElement($key);
            if ($element) {
                $data = $element->getElementData($key, $type);
                $result[] = $data;
            }
        }
        return $this->jsonEncoder->encode($result);
    }

    /**
     * @return string
     */
    public function getMessagesJson()
    {
        return $this->messagesHelper->getMessages();
    }

    /**
     * @return string
     */
    public function getTypeFieldsJson()
    {
        $result = [];
        foreach ($this->types as $key => $value) {
            $result[]= [
                'type' => $key,
                'title' => $value
            ];
        }
        return $this->jsonEncoder->encode($result);
    }

    /**
     * @param $name
     * @return bool|mixed
     */
    protected function _createElement($name)
    {
        $className = 'Amasty\Customform\Block\Widget\Form\Element\\' . ucfirst($name);
        if (class_exists($className)) {
            $element = $this->objectManager->create($className);
            return $element;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getGoogleKey()
    {
        return $this->helper->getGoogleKey();
    }

    /**
     * @return string
     */
    public function getInputFormat()
    {
        return DateFormat::FORMATS[$this->helper->getDateFormat()]['label'] ?? 'mm/dd/yy';
    }

    /**
     * @return \Amasty\Customform\Model\Form
     */
    public function getFormModel()
    {
        return $this->registry->registry('amasty_customform_form');
    }
}
