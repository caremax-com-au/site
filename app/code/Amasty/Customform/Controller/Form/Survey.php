<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Form;

use Amasty\Customform\Model\SurveyAvailableResolver;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Amasty\Customform\Helper\Data;

class Survey extends Action
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var SurveyAvailableResolver
     */
    private $surveyAvailableResolver;

    public function __construct(
        Context $context,
        Data $helper,
        SurveyAvailableResolver $surveyAvailableResolver,
        JsonFactory $resultJsonFactory
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
        $this->surveyAvailableResolver = $surveyAvailableResolver;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $formId = (int)$this->getRequest()->getParam('form_id');
        $result = $this->resultJsonFactory->create();
        $isSurveyAvailable = $formId ? $this->surveyAvailableResolver->isSurveyAvailable($formId) : true;
        $result->setData(['isSurveyAvailable' => $isSurveyAvailable]);

        return $result;
    }
}
