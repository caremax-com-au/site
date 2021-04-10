<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Design implements ArrayInterface
{
    const DEFAULT_THEME = 0;
    const LINEAR_THEME = 1;
    const CIRCLE_THEME = 2;

    const DEFAULT_THEME_CLASS = 'default';
    const LINEAR_THEME_CLASS = 'linear-theme';
    const CIRCLE_THEME_CLASS = 'circle-theme';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::DEFAULT_THEME, 'label' => __('Default')],
            ['value' => self::LINEAR_THEME, 'label' => __('Linear Theme')],
            ['value' => self::CIRCLE_THEME, 'label' => __('Circle Theme')],
        ];
    }
}
