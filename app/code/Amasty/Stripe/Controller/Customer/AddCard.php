<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Controller\Customer;

class AddCard extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Amasty\Stripe\Model\StripeAccountManagement
     */
    private $stripeAccountManagement;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\Stripe\Model\StripeAccountManagement $stripeAccountManagement,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->stripeAccountManagement = $stripeAccountManagement;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $source = $this->getRequest()->getParam('source');
        $explodedSource = explode(":", $source);
        $this->stripeAccountManagement->process($explodedSource, 'save', true);

        $cardsData = $this->stripeAccountManagement->getAllCards();

        return $this->jsonFactory->create()->setData($cardsData);
    }
}
