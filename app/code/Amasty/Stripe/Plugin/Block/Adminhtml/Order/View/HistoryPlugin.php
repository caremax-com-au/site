<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Plugin\Block\Adminhtml\Order\View;

use Amasty\Stripe\Model\Ui\ConfigProvider;
use Magento\Sales\Block\Adminhtml\Order\View\History;

class HistoryPlugin
{
    /**
     * @param History $object
     * @param array $result
     *
     * @return array
     */
    public function afterGetStatuses(History $object, $result)
    {
        $statuses = [];

        /** @var \Magento\Sales\Model\Order $order */
        $order = $object->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();

        if ($paymentMethod === ConfigProvider::CODE && is_array($result)) {
            $statuses['pending'] = 'Pending';
            $statuses['processing'] = 'Processing';
            $statuses += $result;
        } else {
            return $result;
        }

        return $statuses;
    }
}
