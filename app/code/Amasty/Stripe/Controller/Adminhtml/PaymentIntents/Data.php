<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Controller\Adminhtml\PaymentIntents;

use Amasty\Stripe\Model\PaymentIntentRegistry;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Backend\Model\Session\Quote;
use Psr\Log\LoggerInterface;

class Data extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultPageFactory;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var Quote
     */
    private $backendQuoteSession;

    /**
     * @var PaymentIntentRegistry
     */
    private $paymentIntentRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        JsonFactory $resultPageFactory,
        Validator $formKeyValidator,
        Quote $backendQuoteSession,
        PaymentIntentRegistry $paymentIntentRegistry,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->resultPageFactory = $resultPageFactory;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->paymentIntentRegistry = $paymentIntentRegistry;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultPageFactory->create();
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $result->setData(
                [
                    'error' => true,
                    'message' => __("Invalid Form Key")
                ]
            );
        }
        if ($this->getRequest()->isAjax()) {
            try {
                $quote = $this->backendQuoteSession->getQuote();
                $quote->collectTotals();
                $grandTotal = $quote->getBaseGrandTotal();
                $currency = $quote->getBaseCurrencyCode();
                $clientSecret = $this->paymentIntentRegistry->getPaymentIntentsDataSecret($grandTotal, $currency);
                if ($clientSecret) {
                    return $result->setData(
                        [
                            'success' => true,
                            'error' => false,
                            'clientSecret' => $clientSecret['secret'],
                            'paymentIntent' => $clientSecret['pi']
                        ]
                    );
                }
            } catch (\Stripe\Exception\ApiErrorException $stripeException) {
                $this->logger->error($stripeException->getMessage());

                return $result->setData(
                    [
                        'success' => false,
                        'error' => true,
                        'message' => $stripeException->getMessage()
                    ]
                );
            } catch (\Stripe\Error\Base $stripeException) {
                $this->logger->error($stripeException->getMessage());

                return $result->setData(
                    [
                        'success' => false,
                        'error' => true,
                        'message' => $stripeException->getMessage()
                    ]
                );
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());

                return $result->setData(
                    [
                        'success' => false,
                        'error' => true,
                        'message' => __(
                            'I\'m sorry - but we are not able to complete your transaction. '
                            . 'Please contact us so we can assist you.'
                        )
                    ]
                );
            }
        }
        return false;
    }
}
