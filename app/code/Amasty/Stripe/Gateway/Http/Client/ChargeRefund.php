<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Gateway\Http\Client;

/**
 * Charge Refund in stripe
 */
class ChargeRefund extends AbstractClient
{
    /**
     * Create Refund
     *
     * @param array $data
     */
    protected function process(array $data)
    {
        return $this->adapter->refundCreate($data);
    }
}
