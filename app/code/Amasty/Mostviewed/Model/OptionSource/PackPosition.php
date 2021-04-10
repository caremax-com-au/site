<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class PackPosition implements OptionSourceInterface
{
    const PRODUCT_INFO = 'below';

    const TAB = 'tab';

    const CUSTOM_POSITION = 'custom';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PRODUCT_INFO, 'label' => __('Below Product Info')],
            ['value' => self::TAB, 'label' => __('Product Tab')],
            ['value' => self::CUSTOM_POSITION, 'label' => __('Custom Position')]
        ];
    }
}
