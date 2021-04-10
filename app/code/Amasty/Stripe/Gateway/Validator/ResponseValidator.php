<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Gateway\Validator;

/**
 * Response Validator
 */
class ResponseValidator extends GeneralResponseValidator
{
    /**
     * Key get failed status
     */
    const STATUS_FAILED = 'failed';

    /**
     * @return array
     */
    protected function getResponseValidators()
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                function ($response) {
                    return [
                        ($response instanceof \Stripe\Charge || $response instanceof \Stripe\PaymentIntent)
                        && isset($response->status)
                        && $response->status != self::STATUS_FAILED,
                        [__('Wrong transaction status')],
                    ];
                },
            ]
        );
    }
}
