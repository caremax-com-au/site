<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Save Card In Config Field Data
 */
class SaveCards implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Disabled')], ['value' => 1, 'label' => __('Ask the customer')]];
    }
}
