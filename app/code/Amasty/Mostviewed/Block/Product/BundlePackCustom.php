<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Block\Product;

use Magento\Widget\Block\BlockInterface;

class BundlePackCustom extends BundlePack implements BlockInterface
{
    /**
     * @return string
     */
    public function toHtml()
    {
        $html = '';
        if ($this->isBundlePacksExists()) {
            $html = $this->getParentHtml();
        }

        return $html;
    }
}
