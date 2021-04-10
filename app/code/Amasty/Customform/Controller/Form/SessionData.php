<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Controller\Form;

use Amasty\Customform\Model\VariablesSetter;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class SessionData extends Action
{
    const AM_CUSTOM_FORM_SESSION_DATA = 'am_custom_form_session_data';

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $session;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Amasty\Customform\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Customform\Model\FormRepository
     */
    private $formRepository;

    /**
     * @var VariablesSetter
     */
    private $variablesSetter;

    public function __construct(
        Context $context,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Amasty\Customform\Model\FormRepository $formRepository,
        \Amasty\Customform\Helper\Data $helper,
        VariablesSetter $variablesSetter
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        $this->formRepository = $formRepository;
        $this->helper = $helper;
        $this->variablesSetter = $variablesSetter;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            return;
        }
        $result = $this->resultJsonFactory->create();

        $result->setData($this->getPreselectData());

        return $result;
    }

    /**
     * @return array
     */
    private function getPreselectData()
    {
        $preselectData = $this->session->getData(self::AM_CUSTOM_FORM_SESSION_DATA);
        $formId = (int)$this->getRequest()->getParam('form_id');
        if ($formId) {
            if (isset($preselectData['form_id']) && $preselectData['form_id'] != $formId) {
                $this->session->setData(SessionData::AM_CUSTOM_FORM_SESSION_DATA, []);
                $preselectData = [];
            }

            if (!$preselectData) {
                $preselectData = $this->getCustomerPreselectData($formId);
            }
        }

        return $preselectData;
    }

    /**
     * @param int $formId
     * @return array
     */
    private function getCustomerPreselectData(int $formId): array
    {
        $result['form_id'] = $formId;
        $form = $this->getForm($formId);
        $formData = $form ? $this->helper->decode($form->getOrigFormJson()) : [];
        foreach ($formData as $page) {
            foreach ($page as $field) {
                if (isset($field['value']) && $field['value']) {
                    $data = $this->variablesSetter->replaceVariables($field['value']);
                    $result[$field['name']] = $data;
                }
            }
        }

        return $result;
    }

    /**
     * @param int $id
     * @return \Amasty\Customform\Api\Data\FormInterface|null
     */
    private function getForm(int $id)
    {
        try {
            $form = $this->formRepository->get($id);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $form = null;
        }

        return $form;
    }
}
