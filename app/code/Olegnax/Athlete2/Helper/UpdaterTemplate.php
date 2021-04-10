<?php


namespace Olegnax\Athlete2\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;

class UpdaterTemplate extends Helper
{
    const XML_PATH_HEADER_HEDER = 'header/header_layout';
    const DEFAULT_HEADER_HEDER = '1';
    const TPL_PATH_HEADER = "html/header/%s.phtml";

    const XML_PATH_LISTING_PRODUCTS = 'products_listing/products_layout';
    const TPL_PATH_PRODUCT_LIST = "Magento_Catalog::product/list/%s.phtml";
    const TPL_PATH_PRODUCT_LIST_ITEM = "Magento_Catalog::product/list/items/%s.phtml";

    public function setHeaderLayout()
    {
        return sprintf(
            static::TPL_PATH_HEADER,
            $this->getModuleConfig(static::XML_PATH_HEADER_HEDER)
        );
    }

    public function setSearchProductsLayout()
    {
        return $this->setProductsLayout();
    }

    public function setProductsLayout()
    {
        return sprintf(
            static::TPL_PATH_PRODUCT_LIST,
            $this->getModuleConfig(static::XML_PATH_LISTING_PRODUCTS) ?: static::DEFAULT_HEADER_HEDER
        );
    }

    public function setUpSellProductsLayout()
    {
        return $this->setRelatedProductsLayout();
    }

    public function setRelatedProductsLayout()
    {
        return sprintf(
            static::TPL_PATH_PRODUCT_LIST_ITEM,
            $this->getModuleConfig(static::XML_PATH_LISTING_PRODUCTS) ?: '1'
        );
    }

    public function setCrossSellProductsLayout()
    {
        return $this->setRelatedProductsLayout();
    }

    public function setGalleryTemplate()
    {
		$fullActionName = ObjectManager::getInstance()->get(RequestInterface::class)->getFullActionName();
        /* 
         if ($fullActionName !== 'ox_quickview_catalog_product_view' &&
            !($this->getModuleConfig('product/gallery_mobile') && $this->isMobile())
        ) {
            $value = $this->getModuleConfig('product/gallery_layout');
            if ($value == '1col' || $value == '2cols') {
                $value = 'noslider';
            }
        }*/
		$value = 'fast';
		if ($fullActionName == 'catalog_product_view'){
			$value = $this->getModuleConfig('product/gallery_layout') ?: 'fast';
			if ($value == '1col' || $value == '2cols') {
				$value = 'noslider';
			}
		}
        return sprintf('Magento_Catalog::product/view/gallery-%s.phtml', $value );
    }

}