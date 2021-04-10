<?php

/**
 * Athlete2 Theme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Helper;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection;

class ProductList extends Helper
{

    /**
     * @param Product $product
     * @return Category|null
     */
    public function getLastCategory($product)
    {
        /** @var Collection $categoryCollection */
        $categoryCollection = $product->getCategoryCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', '1');
        $category = null;
        if ($categoryCollection->getSize()) {
            $category = null;
            /** @var Category $_category */
            foreach ($categoryCollection as $_category) {
                $size_path = count(explode('/', $_category->getPath()));
                $_size_path = 0;
                if (!empty($category)) {
                    $_size_path = count(explode('/', $category->getPath()));
                }
                if ($_size_path < $size_path) {
                    $category = $_category;
                }
            }
        }
        return $category;
    }

}
