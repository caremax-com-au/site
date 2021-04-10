<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Gateway\Request;

use Amasty\Stripe\Gateway\Config\Config;
use Amasty\Stripe\Gateway\Helper\AmountHelper;
use Amasty\Stripe\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Build data for payment
 */
class PaymentDataBuilder implements BuilderInterface
{
    /**
     * Key for get amount
     */
    const AMOUNT = 'amount';

    /**
     * Key for get currency
     */
    const CURRENCY = 'currency';

    /**
     * Key for get capture
     */
    const CAPTURE = 'capture';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var AmountHelper
     */
    protected $amountHelper;

    /**
     * @param Config $config
     * @param AmountHelper $amountHelper
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        Config $config,
        AmountHelper $amountHelper,
        SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->amountHelper = $amountHelper;
    }

    /**
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        // Prepare payload
        $amount = $this->amountHelper->getAmountForStripe(
            $this->subjectReader->readAmount($buildSubject),
            $order->getCurrencyCode()
        );

        $data = [
            self::AMOUNT => $amount,
            self::CURRENCY => $order->getCurrencyCode(),
            self::CAPTURE => false,
        ];

        return $data;
    }
}
