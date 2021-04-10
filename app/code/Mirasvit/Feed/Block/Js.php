<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.30
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Block;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\Template;

class Js extends Template
{
    /**
     * {@inheritdoc}
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function _toHtml()
    {
        $initObject = [
            'feedReport' => [
            ]
        ];

        return '<div data-mage-init=\'' . json_encode($initObject) . '\'></div>';
    }
}
