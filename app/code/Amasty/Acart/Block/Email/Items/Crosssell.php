<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Block\Email\Items;

use Magento\Catalog\Model\Product\LinkFactory as ProductLinkFactory;

class Crosssell extends Link
{
    /**
     * @return ProductLinkFactory
     */
    protected function getLinkModel()
    {
        return $this->productLinkFactory->create()->useCrossSellLinks();
    }
}
