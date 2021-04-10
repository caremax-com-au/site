<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Block\Email\Items;

use Amasty\Acart\Model\Config;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory as LinkCollectionFactory;
use Magento\Catalog\Model\Product\LinkFactory as ProductLinkFactory;
use Magento\Catalog\Model\Product\Visibility;

abstract class Link extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LinkCollectionFactory
     */
    private $linkCollectionFactory;

    /**
     * @var ProductLinkFactory
     */
    protected $productLinkFactory;

    public function __construct(
        Config $config,
        LinkCollectionFactory $linkCollectionFactory,
        ProductLinkFactory $productLinkFactory,
        Template\Context $context,
        array $data = []
    ) {
        $this->config = $config;
        $this->linkCollectionFactory = $linkCollectionFactory;
        $this->productLinkFactory = $productLinkFactory;
        parent::__construct($context, $data);
    }

    public function getItems()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getData('quote');
        if (!$quote) {
            return [];
        }
        $productIds = $quote->getItemsCollection()->getColumnValues('product_id');
        $collection = $this->linkCollectionFactory->create()
            ->addProductFilter($productIds)
            ->setVisibility([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH])
            ->setLinkModel($this->getLinkModel())
            ->setPositionOrder()
            ->setGroupBy();

        $qty = (int)$this->config->getProductsQty();
        if ($qty) {
            $collection->setPageSize($qty);
        }

        return $collection;
    }

    abstract protected function getLinkModel();
}
