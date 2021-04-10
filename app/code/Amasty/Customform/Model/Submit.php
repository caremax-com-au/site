<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model;

use Amasty\Customform\Helper\Data;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;

class Submit
{
    /**
     * @var AnswerRepository
     */
    private $answerRepository;

    /**
     * @var AnswerFactory
     */
    private $answerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var FormRepository
     */
    private $formRepository;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $session;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var Mail\Notification
     */
    private $notification;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    private $params;

    public function __construct(
        \Amasty\Customform\Model\FormRepository $formRepository,
        \Amasty\Customform\Model\AnswerRepository $answerRepository,
        \Amasty\Customform\Model\AnswerFactory $answerFactory,
        Data $helper,
        Escaper $escaper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Amasty\Customform\Model\Mail\Notification $notification,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->answerRepository = $answerRepository;
        $this->answerFactory = $answerFactory;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->escaper = $escaper;
        $this->redirect = $redirect;
        $this->formRepository = $formRepository;
        $this->session = $session;
        $this->formKeyValidator = $formKeyValidator;
        $this->notification = $notification;
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    public function process(array $params): string
    {
        $this->params = $params;
        if ($this->validateData($params)) {
            /** @var Form $formModel */
            $formModel = $this->formRepository->get((int) $params['form_id']);
            $model = $this->submit($formModel);

            if ($formModel->getEmailField()) {
                $params['email'] = $params[$formModel->getEmailField()] ?? '';
                $this->request->setParams($params);
            }

            $this->session->unsFormData();

            $url = $formModel->getSuccessUrl();
            if ($url && $url != '/') {
                $url = trim($url, '/');
            }

            $this->notification->sendNotifications($formModel, $model);
            $this->showSuccessMessage($formModel);
        }

        return $url ?? Data::REDIRECT_PREVIOUS_PAGE;
    }

    public function submit(Form $formModel)
    {
        /** @var  Answer $model */
        $model = $this->answerFactory->create();
        $answerData = $this->generateAnswerData($formModel);
        $model->addData($answerData);
        $model->setAdminResponseEmail($model->getRecipientEmail());
        $this->answerRepository->save($model);

        return $model;
    }

    private function showSuccessMessage(Form $formModel)
    {
        $message = $formModel->getSuccessMessage();
        if ($message) {
            $this->messageManager->addSuccessMessage(
                $message
            );
        }
    }

    /**
     * @param array $params
     * @return bool
     * @throws LocalizedException
     * @throws ValidatorException
     */
    private function validateData(array $params)
    {
        if (!isset($params['form_id'])) {
            throw new LocalizedException(__('form_id is not resolved.'));
        }

        if (!$this->isValidFormKey()) {
            throw new LocalizedException(
                __('Form key is not valid. Please try to reload the page.')
            );
        }

        if ($this->helper->isGDPREnabled() && isset($params['gdpr']) && !$params['gdpr']) {
            throw new LocalizedException(__('Please agree to the Privacy Policy'));
        }

        $fileFields = $this->request->getFiles();
        if ($fileFields && $fileFields->count()) {
            $this->validateFiles($fileFields->toArray());
        }

        return true;
    }

    public function isValidFormKey(): bool
    {
        return $this->formKeyValidator->validate($this->request);
    }

    /**
     * @param array $fileFields
     *
     * @throws LocalizedException
     * @throws ValidatorException
     */
    private function validateFiles($fileFields)
    {
        foreach ($fileFields as $files) {
            foreach ($files as $file) {
                $errorCode = $file['error'] ?? 0;

                switch ($errorCode) {
                    case UPLOAD_ERR_FORM_SIZE:
                    case UPLOAD_ERR_INI_SIZE:
                        $fileName = $file['name'] ?: '';
                        throw new LocalizedException(
                            __(
                                'File with name "%1" exceeds the allowed file size. Form was not submitted',
                                $fileName
                            )
                        );
                    case UPLOAD_ERR_CANT_WRITE:
                        throw new ValidatorException(__('File upload error. Failed to write file to disk'));
                    case UPLOAD_ERR_NO_TMP_DIR:
                        throw new ValidatorException(__('File upload error. Missing a temporary folder'));
                    case UPLOAD_ERR_PARTIAL:
                        throw new ValidatorException(
                            __('File upload error.  The uploaded file was only partially uploaded')
                        );
                }
            }
        }
    }

    private function generateAnswerData($formModel)
    {
        $json = $this->generateJson($formModel);
        $data = [
            'form_id' => $formModel->getId(),
            'store_id' => $this->storeManager->getStore()->getId(),
            'ip' => $this->helper->getCurrentIp(),
            'customer_id' => (int)$this->helper->getCurrentCustomerId(),
            'response_json' => $json
        ];

        if ($formModel->getSaveRefererUrl()) {
            $data['referer_url'] = $this->addRefererUrlIfNeed();
        }

        return $data;
    }

    public function addRefererUrlIfNeed(): string
    {
        return $this->escaper->escapeUrl($this->redirect->getRefererUrl());
    }

    /**
     * @param $formModel
     * @return string
     * @throws LocalizedException
     */
    private function generateJson($formModel)
    {
        $formJson = $formModel->getFormJson();
        $pages = $this->helper->decode($formJson);
        $data = [];

        foreach ($pages as $page) {
            if (isset($page['type'])) {
                // support for old versions of forms
                $data = $this->dataProcessing($page, $data);
            } else {
                foreach ($page as $field) {
                    $data = $this->dataProcessing($field, $data);
                }
            }
        }
        if ($productId = isset($this->params['hide_product_id']) ? $this->params['hide_product_id'] : null) {
            $data['hide_product_id'] = [
                'value' => $productId,
                'label' => __('Requested Product'),
                'type' => 'textinput'
            ];
        }

        return $this->helper->encode($data);
    }

    /**
     * @param $data
     * @param $record
     *
     * @return mixed
     * @throws LocalizedException
     */
    private function dataProcessing($data, $record)
    {
        $name = $data['name'];
        $value = $this->getValidValue($data, $name);
        if ($value) {
            $type = $data['type'];
            switch ($type) {
                case 'googlemap':
                    $record[$name]['value'] = $value;
                    break;
                case 'checkbox':
                case 'checkboxtwo':
                case 'dropdown':
                case 'listbox':
                case 'radio':
                case 'radiotwo':
                    $tmpValue = [];

                    foreach ($data['values'] as $option) {
                        if (is_array($value) && in_array($option['value'], $value)) {
                            $tmpValue[] = $option['label'];
                        } elseif ($value == $option['value']) {
                            $tmpValue[] = $option['label'];
                            break;
                        }
                    }

                    $record[$name]['value'] = $tmpValue ? implode(', ', $tmpValue) : $value;
                    break;
                default:
                    $value = $this->helper->escapeHtml($value);
                    $record[$name]['value'] = $value;
            }

            $record[$name]['label'] = $data['label'];
            $record[$name]['type'] = $type;
        }

        return $record;
    }

    /**
     * @param $field
     * @param $name
     * @return array|mixed
     * @throws LocalizedException
     */
    private function getValidValue($field, $name)
    {
        $result = isset($this->params[$name]) ? $this->params[$name] : '';
        $fileValidation = [];
        $validation = $this->helper->generateValidation($field);
        $fieldType = $this->getRow($field, 'type');
        $isFile = strcmp($fieldType, 'file') === 0;
        $isMultiple = (bool)$this->getRow($field, 'multiple');
        $filesArray = $isFile ? $this->request->getFiles()->toArray() : [];
        $isFilesEmpty = $isFile ? $this->isEmptyFiles($filesArray, $name, $isMultiple) : false;

        if ($validation) {
            $valueNotExist = (!$isFile && !$result) || ($isFile && $isFilesEmpty);

            if (!array_key_exists('required', $validation) && $valueNotExist) {
                return $result;
            }

            $this->validateField($field, $fieldType, $validation, $result, $fileValidation);
        }

        if ($fieldType == 'googlemap' && $result) {
            $coordinates = explode(', ', trim($result, '()'));

            if (!isset($coordinates[0]) || !isset($coordinates[1])) {
                $coordinates = [0, 0];
            }

            $result = $this->helper->encode(
                [
                    'position' => [
                        'lat' => (float)$coordinates[0],
                        'lng' => (float)$coordinates[1]
                    ],
                    'zoom' => (int)$field['zoom']
                ]
            );
        }

        if ($isFile && !$this->request->isAjax() && !$this->isHiddenField($field)) {
            if ($isMultiple) {
                $result = [];
                $filesFromRequest = $this->request->getFiles()->toArray();

                if (isset($filesFromRequest) && key_exists($name, $filesFromRequest)) {
                    foreach ($filesFromRequest[$name] as $key => $tmpFile) {
                        $tmpName = $name . "[$key]";
                        $result[] = $this->helper->saveFileField($tmpName, $fileValidation);
                    }
                }
            } else {
                $result = $this->helper->saveFileField($name, $fileValidation);
            }
        }

        return $result;
    }

    /**
     * @param $filesArray
     * @param $name
     * @param $isMultiple
     * @return bool
     */
    private function isEmptyFiles($filesArray, $name, $isMultiple)
    {
        $hasName = array_key_exists($name, $filesArray);
        $error = $hasName ? UPLOAD_ERR_OK : UPLOAD_ERR_NO_FILE;

        if ($isMultiple && $hasName) {
            $file = array_pop($filesArray[$name]);
            $error = $file['error'] ?? 0;
        } elseif ($hasName) {
            $error = $filesArray[$name]['error'] ?? 0;
        }

        return $error == UPLOAD_ERR_NO_FILE;
    }

    /**
     * @param $field
     * @param $fieldType
     * @param $validation
     * @param $value
     * @param $fileValidation
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function validateField($field, $fieldType, $validation, $value, &$fileValidation)
    {
        foreach ($validation as $key => $item) {
            switch ($key) {
                case 'required':
                    if ($fieldType == 'file') {
                        $fileValidation[$key] = true;
                    }
                    if ($value === '' && ($fieldType != 'file')) {
                        if ($this->isHiddenField($field)) {
                            continue 2;
                        }
                        $name = isset($field['title']) ? $field['title'] : $field['label'];
                        throw new LocalizedException(__('Please enter a %1.', $name));
                    }
                    break;
                case 'validation':
                    if ($item == 'validate-email' && !$this->isHiddenField($field)) {
                        $value = filter_var($value, FILTER_SANITIZE_EMAIL);
                        if (!\Zend_Validate::is($value, 'EmailAddress')) {
                            throw new LocalizedException(__('Please enter a valid email address.'));
                        }
                    }
                    break;
                case 'allowed_extension':
                case 'max_file_size':
                    $fileValidation[$key] = $item;
                    break;
            }
        }
    }

    private function getRow($field, $type)
    {
        return isset($field[$type]) ? $field[$type] : null;
    }

    /**
     * field hidden by dependency
     *
     * @param array $field
     *
     * @return bool
     */
    private function isHiddenField($field)
    {
        $isHidden = false;
        if (isset($field['dependency']) && $field['dependency']) {
            foreach ($field['dependency'] as $dependency) {
                if (isset($dependency['field'])
                    && isset($dependency['value'])
                    && isset($this->params[$dependency['field']])
                    && $this->params[$dependency['field']] != $dependency['value']
                ) {
                    $isHidden = true;
                }
            }
        }
        if (!$isHidden) {
            $emailField = $this->formRepository->get(
                (int)$this->params['form_id']
            )->getEmailField();
            if ($emailField == $field['name'] && $this->helper->getCurrentCustomerId()) {
                $isHidden = true;
            }
        }

        return $isHidden;
    }
}
