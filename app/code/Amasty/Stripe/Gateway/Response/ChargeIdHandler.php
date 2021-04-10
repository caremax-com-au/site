<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Amasty\Stripe\Gateway\Helper\SubjectReader;

/**
 * Charge handler for invoice
 */
class ChargeIdHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $chargeFlag = false;
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        if ($paymentDO->getPayment() instanceof Payment) {
            /** @var \Stripe\Charge $charge */
            $charge = $this->subjectReader->readCharge($response);

            /** @var Payment $orderPayment */
            $orderPayment = $paymentDO->getPayment();
            $this->setChargeId(
                $orderPayment,
                $charge
            );

            $chargeId = $charge->id;
            $orderPayment->setAdditionalInformation("stripe_charge_id", $chargeId);
            if (!empty($charge->charges->data[0]->id)) {
                $transactionId = $charge->charges->data[0]->id;

                $orderPayment->setTransactionId($transactionId)
                    ->setLastTransId($transactionId);
                $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());
                $orderPayment->setShouldCloseParentTransaction($this->shouldCloseParentTransaction($orderPayment));
                $chargeFlag = true;
            }

            if (!$chargeFlag) {
                throw new LocalizedException(
                    __("Payment failed")
                );
            }
        }
    }

    /**
     * @param Payment $orderPayment
     * @param \Stripe\Charge $charge
     * @return void
     */
    protected function setChargeId(
        Payment $orderPayment,
        $charge
    ) {
        $orderPayment->setTransactionId($charge->id);
    }

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction()
    {
        return false;
    }

    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $orderPayment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return false;
    }
}
