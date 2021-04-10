<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Amasty\Stripe\Gateway\Config\Config;
use Amasty\Stripe\Gateway\Helper\SubjectReader;

/**
 * Set additional information to payment
 */
class CardDetailsHandler implements HandlerInterface
{
    /**
     * Card Brand Logo
     */
    const CARD_BRAND = 'brand';

    /**
     * Expire Month For Card
     */
    const CARD_EXP_MONTH = 'exp_month';

    /**
     * Expire Year For Card
     */
    const CARD_EXP_YEAR = 'exp_year';

    /**
     * Last Four Numbers Card
     */
    const CARD_LAST4 = 'last4';

    /**
     * CC Number Card
     */
    const CARD_NUMBER = 'cc_number';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $charge = $this->subjectReader->readCharge($response);
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        if (!empty($charge->charges->data[0]) && !empty($charge->charges->data[0]->payment_method_details->card)) {
            $card = $charge->charges->data[0]->payment_method_details->card;
            $payment->setCcLast4($card->last4);
            $payment->setCcExpMonth($card->exp_month);
            $payment->setCcExpYear($card->exp_year);
            $payment->setCcType($card->brand);

            // set card details to additional info
            $payment->setAdditionalInformation(self::CARD_NUMBER, 'xxxx-' . $card->last4);
            $payment->setAdditionalInformation(OrderPaymentInterface::CC_TYPE, $card->brand);
        }
    }
}
