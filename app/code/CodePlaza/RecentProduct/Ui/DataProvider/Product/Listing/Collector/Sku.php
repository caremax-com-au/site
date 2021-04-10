<?php
namespace CodePlaza\RecentProduct\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderExtensionFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;

class Sku implements ProductRenderCollectorInterface
{
    /** SKU html key */
    const KEY = "sku";

    /**
     * @var ProductRenderExtensionFactory
     */
    private $productRenderExtensionFactory;

    /**
     * Sku constructor.
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
        $extensionAttributes = $productRender->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->productRenderExtensionFactory->create();
        }

        if($product->getSku())
        {
            $extensionAttributes
                ->setSku($product->getSku());
        }

        $productRender->setExtensionAttributes($extensionAttributes);
    }
}