<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Ui\Component\Listing\Column\History\Status;

use Amasty\Acart\Model\History as History;
use Amasty\Acart\Model\RuleQuote;
use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    public function toArray()
    {
        return [

        ];
    }

    public function toOptionArray()
    {
        return [
            [
                'value' => History::STATUS_PROCESSING,
                'label' => __("Not sent")
            ],
            [
                'value' => History::STATUS_SENT,
                'label' => __("Sent")
            ],
            [
                'value' => History::STATUS_CANCEL_EVENT,
                'label' => __("Cancel Condition")
            ],
            [
                'value' => History::STATUS_BLACKLIST,
                'label' => __("Blacklist")
            ],
            [
                'value' => History::STATUS_ADMIN,
                'label' => __("Canceled by the admin")
            ],
            [
                'value' => History::STATUS_NOT_NEWSLETTER_SUBSCRIBER,
                'label' => __("Customer is Not Newsletter Subscriber")
            ],
            [
                'value' => RuleQuote::COMPLETE_QUOTE_REASON_PLACE_ORDER,
                'label' => __("Order Placed")
            ],
        ];
    }
}
