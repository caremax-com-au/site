<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model\Config\Source;

class DateFormat implements \Magento\Framework\Option\ArrayInterface
{
    const FORMATS = [
        'yy-mm-dd' => [
            'label' => 'yyyy-mm-dd',
            'format' => 'Y-m-d'
        ],
        'mm/dd/yy' => [
            'label' => 'mm/dd/yyyy',
            'format' => 'm/d/Y'
        ],
        'dd/MM/yy' => [
            'label' => 'dd/mm/yyyy',
            'format' => 'd/F/Y'
        ],
        'd/M/yy' => [
            'label' => 'd/m/yyyy',
            'format' => 'j/M/Y'
        ],
        'dd.MM.yy' => [
            'label' => 'dd.mm.yyyy',
            'format' => 'd.F.Y'
        ],
        'dd.MM.y' => [
            'label' => 'dd.mm.yy',
            'format' => 'd.F.y'
        ],
        'd.M.y' => [
            'label' => 'd.m.yy',
            'format' => 'j.M.y'
        ],
        'd.M.yy' => [
            'label' => 'd.m.yyyy',
            'format' => 'j.M.Y'
        ],
        'dd-MM-y' => [
            'label' => 'dd-mm-yy',
            'format' => 'd-F-y'
        ],
        'yy.MM.dd' => [
            'label' => 'yyyy.mm.dd',
            'format' => 'Y.F.d'
        ],
        'dd-MM-yy' => [
            'label' => 'dd-mm-yyyy',
            'format' => 'd-M-Y'
        ],
        'yy/mm/dd' => [
            'label' => 'yyyy/mm/dd',
            'format' => 'Y/m/d'
        ],
        'y/mm/dd' => [
            'label' => 'yy/mm/dd',
            'format' => 'y/m/d'
        ],
        'dd/mm/y' => [
            'label' => 'dd/mm/yy',
            'format' => 'd/m/y'
        ],
        'mm/dd/y' => [
            'label' => 'mm/dd/yy',
            'format' => 'm/d/y'
        ],
        'dd/mm yy' => [
            'label' => 'dd/mm yyyy',
            'format' => 'd/m Y'
        ],
        'yy mm dd' => [
            'label' => 'yyyy mm dd',
            'format' => 'Y m d'
        ],
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach (self::FORMATS as $value => $options) {
            $result[] = [
                'value' => $value,
                'label' => $options['label'].' (' . date($options['format']) . ')'
            ];
        }

        return $result;
    }
}
