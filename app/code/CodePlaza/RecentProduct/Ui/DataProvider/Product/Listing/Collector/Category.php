<?php
namespace CodePlaza\RecentProduct\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderExtensionFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;

class Category implements ProductRenderCollectorInterface
{
    /** CATEGORY html key */
    const KEY = "category";

    /**
     * @var ProductRenderExtensionFactory
     */
    private $productRenderExtensionFactory;

    /**
     * Category constructor.
     * @param ProductRenderExtensionFactory $productRenderExtensionFactory
     */
    public function __construct(
        ProductRenderExtensionFactory $productRenderExtensionFactory
    ) {
        $this->productRenderExtensionFactory = $productRenderExtensionFactory;
    }

    /**
     * @inheritdoc
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $category = [];
        $extensionAttributes = $productRender->getExtensionAttributes();
        
        $category['link'] = "https://phpstack-482142-1517940.cloudwaysapps.com/pain-relief";
        $category['name'] = "test";
        $productRender->setCategory($category);
       
    }
}