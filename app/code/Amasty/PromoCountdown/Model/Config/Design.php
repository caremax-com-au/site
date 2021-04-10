<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class Design implements ArrayInterface
{
    const CLEAR = 'default';

    const TRANSPARENT = 'transparent';

    const CIRCLE = 'circle';

    const HONEYCOMB = 'honeycomb';

    const ROUND = 'round';

    const PROGRESS = 'progress';

    const ANIMATED_DESIGNS = [
        self::CIRCLE,
        self::ROUND,
        self::PROGRESS
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Simple',
                'value' => [
                    [
                        'label' => 'Clear',
                        'value' => self::CLEAR
                    ],
                    [
                        'label' => 'Round',
                        'value' => self::CIRCLE
                    ],
                    [
                        'label' => 'Honeycomb',
                        'value' => self::HONEYCOMB
                    ],
                    [
                        'label' => 'Transparent',
                        'value' => self::TRANSPARENT
                    ],
                ]
            ],
            [
                'label' => 'Round',
                'value' => self::ROUND
            ],
            [
                'label' => 'Progress Bar',
                'value' => self::PROGRESS
            ],
        ];
    }
}
