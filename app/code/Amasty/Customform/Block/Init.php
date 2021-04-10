<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Block;

use Amasty\Customform\Helper\Data;
use Amasty\Customform\Model\Config\Source\DateFormat;
use Magento\Backend\Block\Widget\Grid\Column\Filter\Store;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Init extends Template implements BlockInterface, IdentityInterface
{
    protected $_template = 'Amasty_Customform::init.phtml';

    /**
     * @var \Amasty\Customform\Model\Form
     */
    protected $currentForm;

    /**
     * @var int
     */
    protected $formId;

    /**
     * @var \Amasty\Customform\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Customform\Model\FormRepository
     */
    private $formRepository;

    /**
     * @var bool
     */
    private $useGoogleMap = false;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @var array
     */
    private $additionalClasses = [];

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $helper,
        \Amasty\Customform\Model\FormRepository $formRepository,
        Context $httpContext,
        \Magento\Framework\Data\Form\FormKey $formKey,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->formRepository = $formRepository;
        $this->formKey = $formKey;
        $this->httpContext = $httpContext;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    private function init()
    {
        $form = $this->getCurrentForm();
        $this->addAdditionalClass($form->getDesignClass());
    }

    /**
     * @return bool
     */
    public function isSurvey()
    {
        return $this->getCurrentForm()->isSurveyModeEnabled();
    }

    /**
     * @return \Amasty\Customform\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ($this->validate()) {
            $this->init();
            return parent::toHtml();
        }

        return '';
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    protected function validate()
    {
        $form = $this->getCurrentForm();
        if (!$form || !$form->isEnabled()) {
            return false;
        }

        /* check for store ids*/
        $stores = $form->getStoreId();
        $stores = explode(',', $stores);
        $currentStoreId = $this->_storeManager->getStore()->getId();
        if (!in_array(Store::ALL_STORE_VIEWS, $stores) && !in_array($currentStoreId, $stores)) {
            return false;
        }

        /* check for customer groups*/
        $availableGroups = $form->getCustomerGroup();
        $availableGroups = explode(',', $availableGroups);
        $currentGroup = $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
        if (!in_array($currentGroup, $availableGroups)) {
            return false;
        }

        return true;
    }

    /**
     * @return \Amasty\Customform\Api\Data\FormInterface|\Amasty\Customform\Model\Form|bool
     */
    public function getCurrentForm()
    {
        if ($this->currentForm === null) {
            try {
                $this->currentForm = $this->formRepository->get($this->getFormId());
                $this->updateFormInfo();
            } catch (NoSuchEntityException $e) {
                $this->currentForm = false;
            }
        }

        return $this->currentForm;
    }

    /**
     * @return \Magento\Framework\Phrase|mixed|string
     */
    public function getButtonTitle()
    {
        return $this->getCurrentForm()->getSubmitButton() ?: __('Submit');
    }

    /**
     * @return string
     */
    public function getFormDataJson()
    {
        $formData = $this->getCurrentForm()->getFormJson();
        $formTitles = $this->getCurrentForm()->getFormTitle();

        $result = [
            'dataType' => 'json',
            'formData' => $formData,
            'src_image_progress' => $this->getViewFileUrl('Amasty_Customform::images/loading.gif'),
            'ajax_submit' => $this->getCurrentForm()->getSuccessUrl() == Data::REDIRECT_PREVIOUS_PAGE ? 1 : 0,
            'pageTitles' => $formTitles,
            'submitButtonTitle' => $this->escapeHtml($this->getButtonTitle()),
            'dateFormat' => $this->helper->getDateFormat(),
            'placeholder' => DateFormat::FORMATS[$this->helper->getDateFormat()]['label'] ?? 'mm/dd/yy'
        ];

        return $this->helper->encode($result);
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->helper->getSubmitUrl();
    }

    /**
     * Check if GDPR consent enabled
     *
     * @return bool
     */
    public function isGDPREnabled()
    {
        return $this->helper->isGDPREnabled();
    }

    /**
     * Get text for GDPR
     *
     * @return string
     */
    public function getGDPRText()
    {
        return $this->helper->getGDPRText();
    }

    public function getGdprCheckboxHtml(string $scope): string
    {
        $result = $this->dataObjectFactory->create();
        $this->_eventManager->dispatch(
            'amasty_gdpr_get_checkbox',
            [
                'scope' => $scope,
                'result' => $result
            ]
        );

        return $result->getData('html') ?: '';
    }

    public function updateFormInfo()
    {
        $formData = $this->getCurrentForm()->getFormJson();
        $formData = $this->helper->decode($formData);

        foreach ($formData as $index => &$page) {
            if (isset($page['type'])) {
                // support for old versions of forms
                $this->dataProcessing($page, $index, $formData);
            } else {
                foreach ($page as $key => $value) {
                    $this->dataProcessing($value, $key, $page);
                }
            }
        }

        $this->getCurrentForm()->setFormJson(
            $this->helper->encode(array_values($formData))
        );
    }

    /**
     * @param $data
     * @param $key
     * @param $formData
     */
    private function dataProcessing($data, $key, &$formData)
    {
        $formData[$key]['validation_fields'] = $this->helper->generateValidation($data);

        $hideEmail = $this->getCurrentForm()->isHideEmailField()
            && $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);

        if ($hideEmail && $data['name'] === $this->getCurrentForm()->getEmailField()) {
            unset($formData[$key]);
            $formData = array_values($formData);
        }

        if ($data['type'] == 'googlemap') {
            $this->setUseGoogleMap(true);
        }
    }

    /**
     * @return bool
     */
    public function isUseGoogleMap()
    {
        return $this->useGoogleMap;
    }

    /**
     * @param bool $useGoogleMap
     */
    public function setUseGoogleMap($useGoogleMap)
    {
        $this->useGoogleMap = $useGoogleMap;
    }

    /**
     * @return mixed
     */
    public function getGoogleKey()
    {
        return $this->helper->getGoogleKey();
    }

    /**
     * @return bool
     */
    public function isPopupUsed()
    {
        return $this->getCurrentForm()->isPopupShow();
    }

    /**
     * @return string
     */
    public function getTriggerPopup()
    {
        return strip_tags($this->getCurrentForm()->getPopupButton());
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return string
     */
    public function getAdditionalClasses()
    {
        return implode(' ', $this->additionalClasses);
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function addAdditionalClass($class)
    {
        $this->additionalClasses[] = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionUrl()
    {
        return $this->getUrl('amasty_customform/form/sessiondata');
    }

    /**
     * @return int
     */
    public function getFormId()
    {
        return $this->formId ?: $this->getData('form_id');
    }

    /**
     * @param int $formId
     */
    public function setFormId(int $formId): void
    {
        $this->formId = $formId;
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return $this->getCurrentForm() ? $this->getCurrentForm()->getIdentities() : [];
    }
}
