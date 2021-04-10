<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Gateway\Http\Client;

use Amasty\Stripe\Gateway\Request\ChargeCaptureDataBuilder;

/**
 * Charge Invoice in Stripe
 */
class ChargeCapture extends AbstractClient
{
    /**
     * @param array $data
     *
     * @return \Stripe\ApiResource|\Stripe\Error\Base
     */
    protected function process(array $data)
    {
        $chargeId = $data[ChargeCaptureDataBuilder::CHARGE_ID];
        unset($data[ChargeCaptureDataBuilder::CHARGE_ID]);

        return $this->adapter->chargeCapture(
            $chargeId,
            $data
        );
    }
}
