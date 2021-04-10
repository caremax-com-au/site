<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Mail;

use Amasty\Customform\Block\Adminhtml\Form\Edit\Tab\Main;
use Amasty\Customform\Helper\Data;
use Amasty\Customform\Model\Answer;
use Amasty\Customform\Model\Form;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\MediaStorage\Model\File\Uploader;

class Notification
{
    /**
     * @var \Amasty\Customform\Model\Template\TransportBuilderFactory
     */
    private $transportBuilder;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Amasty\Customform\Model\Template\TransportBuilderFactory $transportBuilderFactory,
        Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        File $fileDriver,
        Escaper $escaper
    ) {
        $this->transportBuilder = $transportBuilderFactory->create();
        $this->helper = $helper;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
    }

    public function sendNotifications(Form $formModel, Answer $model)
    {
        $this->sendAdminNotification($formModel, $model);
        $this->sendAutoReply($formModel, $model);
    }

    /**
     * @param Form $formModel
     * @param Answer $model
     */
    private function sendAdminNotification(Form $formModel, Answer $model)
    {
        $emailTo = trim((string)$formModel->getSendTo());
        $globalEmailTo = $this->helper->getModuleConfig('email/recipient_email');
        if (!$emailTo && $globalEmailTo) {
            $emailTo = trim($globalEmailTo);
        }

        if ($emailTo && $this->isSendNotification($formModel)) {
            $sender = $this->helper->getModuleConfig('email/sender_email_identity');
            $template = $formModel->getEmailTemplate();
            if (!$template) {
                $template = $this->helper->getModuleConfig('email/template');
            }

            $model->setFormTitle($formModel->getTitle());
            $model->addData($this->getModelFields($model));
            $customerData = $this->helper->getCustomerName($model->getCustomerId());

            try {
                $store = $this->storeManager->getStore();
                $data = [
                    'website_name' => $store->getWebsite()->getName(),
                    'group_name' => $store->getGroup()->getName(),
                    'store_name' => $store->getName(),
                    'response' => $model,
                    'link' => $this->helper->getAnswerViewUrl($model->getAnswerId()),
                    'submit_fields' => $this->getSubmitFields($model),
                    'customer_name' => $customerData['customer_name'],
                    'customer_link' => $customerData['customer_link'],
                ];

                if (strpos($emailTo, ',') !== false) {
                    /*
                     * It's done to bypass the Magento 2.3.3 bug, which makes it impossible to add an array
                     * of mail recipients until you add one recipient
                     */
                    $emailTo = array_map('trim', explode(',', $emailTo));
                    $firstReceiver = array_shift($emailTo);
                    $this->transportBuilder->addTo($firstReceiver);
                }

                $transport = $this->transportBuilder->setTemplateIdentifier(
                    $template
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
                )->setTemplateVars(
                    $data
                )->setFrom(
                    $sender
                )->addTo(
                    $emailTo
                );

                $replyTo = $model->getRecipientEmail();
                if ($replyTo) {
                    $transport->setReplyTo($replyTo);
                }

                $transport->getTransport()->sendMessage();
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('Unable to send the email.'));
            }
        }
    }

    /**
     * @param Form $formModel
     *
     * @return bool
     */
    protected function isSendNotification(Form $formModel)
    {
        $enabled = $formModel->getSendNotification();
        if ($enabled == Main::SYSTEM_CONFIG_VALUE) {
            $enabled = $this->helper->isSendNotification();
        }

        if (!$this->helper->getModuleConfig('email/sender_email_identity')) {
            $this->logger->critical(
                __('Email was not sent. Please specify email sender in Amasty Custom Form module configuration.')
            );
            $enabled = false;
        }

        return $enabled;
    }

    /**
     * @param Answer $model
     *
     * @return array
     */
    protected function getModelFields(Answer $model)
    {
        $data = [];
        $fields = $model->getResponseJson() ? $this->helper->decode($model->getResponseJson()) : [];
        foreach ($fields as $key => $field) {
            $value = $this->getRow($field, 'value');
            if (is_array($value)) {
                $filteredFiles = array_filter($value);
                $value = implode(', ', $filteredFiles);
            }

            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * @param Form $formModel
     * @param Answer $model
     * @throws LocalizedException
     */
    private function sendAutoReply(Form $formModel, Answer $model)
    {
        if (!$this->isAutoReplyEnabled($formModel)) {
            return;
        }

        $emailTo = $model->getRecipientEmail();
        if ($emailTo) {
            $sender = $this->helper->getAutoReplySender();
            $template = $this->getAutoReplyTemplate($formModel);

            $model->setFormTitle($formModel->getTitle());
            $model->addData($this->getModelFields($model));
            $customerData = $this->helper->getCustomerName($model->getCustomerId());

            try {
                $store = $this->storeManager->getStore();
                $data = [
                    'website_name' => $store->getWebsite()->getName(),
                    'group_name' => $store->getGroup()->getName(),
                    'store_name' => $store->getName(),
                    'response' => $model,
                    'customer_name' => $customerData['customer_name'],
                    'form_name' => $formModel->getTitle(),
                    'submit_fields' => $this->getSubmitFields($model)
                ];

                $transport = $this->transportBuilder->setTemplateIdentifier(
                    $template
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
                )->setTemplateVars(
                    $data
                )->setFrom(
                    $sender
                )->addTo(
                    $emailTo
                )->getTransport();

                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('Unable to send the email.'));
            }
        }
    }

    /**
     * @param Form $formModel
     *
     * @return bool
     */
    protected function isAutoReplyEnabled(Form $formModel)
    {
        $enabled = $formModel->isAutoReplyEnabled();
        if ($enabled == Main::SYSTEM_CONFIG_VALUE) {
            $enabled = $this->helper->isAutoReplyEnabled();
        }

        return $enabled;
    }

    /**
     * @param Form $formModel
     *
     * @return string
     */
    protected function getAutoReplyTemplate(Form $formModel)
    {
        $template = $formModel->getAutoReplyTemplate();
        if (!$template || $template == Main::SYSTEM_CONFIG_VALUE) {
            $template = $this->helper->getAutoReplyTemplate();
        }

        return $template;
    }

    /**
     * @param Answer $model
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getSubmitFields(Answer &$model)
    {
        $html = '<table cellpadding="7">';
        $formData = $model->getResponseJson();

        $html .= $formData ? $this->formatFormData($formData) : '';

        $html .= '</table>';

        return $html;
    }

    /**
     * @param $formData
     * @return string
     */
    private function formatFormData($formData)
    {
        $fields = $this->helper->decode($formData);
        $html = '';

        foreach ($fields as $field) {
            $value = $this->getRow($field, 'value');
            $fieldType = $this->getRow($field, 'type');

            if ($fieldType == 'file') {
                if (is_array($value)) {
                    $filteredFiles = array_filter($value);

                    foreach ($filteredFiles as $fileName) {
                        if ($fileName) {
                            $this->addAttachment($fileName);
                        }
                    }
                } else {
                    $this->addAttachment($value);
                }
            }

            if (is_array($value)) {
                $filteredFiles = array_filter($value);
                $value = implode(', ', $filteredFiles);
            }

            $html .= '<tr>'
                . '<td style="width: 50%;">' . $field['label'] . '</td>'
                . '<td>' . $value . '</td>'
                . '</tr>';
        }

        return $html;
    }

    /*
     * @param string
     */
    private function addAttachment($fileName)
    {
        $filePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath()
            . Data::MEDIA_PATH . Uploader::getCorrectFileName($fileName);
        $this->transportBuilder->addAttachment(
            $this->fileDriver->fileGetContents($filePath),
            $fileName
        );
    }

    private function getRow($field, $type)
    {
        return isset($field[$type]) ? $this->escaper->escapeHtml($field[$type]) : null;
    }
}
