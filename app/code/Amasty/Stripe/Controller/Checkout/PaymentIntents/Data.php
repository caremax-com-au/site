<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Controller\Checkout\PaymentIntents;

use Amasty\Stripe\Model\PaymentIntentRegistry;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Psr\Log\LoggerInterface;

/**
 * Generate ClientSecret for Stripe
 */
class Data extends Action
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PaymentIntentRegistry
     */
    private $paymentIntentRegistry;

    public function __construct(
        Context $context,
        CheckoutSession $session,
        JsonFactory $resultJsonFactory,
        Validator $formKeyValidator,
        LoggerInterface $logger,
        PaymentIntentRegistry $paymentIntentRegistry
    ) {
        parent::__construct($context);
        $this->checkoutSession = $session;
        $this->jsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
        $this->paymentIntentRegistry = $paymentIntentRegistry;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
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
                $quote = $this->checkoutSession->getQuote();
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
