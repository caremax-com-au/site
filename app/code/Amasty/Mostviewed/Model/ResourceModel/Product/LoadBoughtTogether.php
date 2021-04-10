<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

class LoadBoughtTogether
{
    const QUERY_LIMIT = 1000;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    public function execute(array $productIds, array $storeIds, int $period, array $orderStatuses): array
    {
        $tableName = $this->resourceConnection->getTableName('sales_order_item');
        $connection = $this->resourceConnection->getConnection();

        $productIdField = $connection->getIfNullSql(
            'parent_product.entity_id',
            'order_item.product_id'
        );

        $orderSelect = $connection->select()
            ->from(['order_item' => $tableName], ['order_item.order_id'])
            ->where('order_item.product_id IN (?)', $productIds)
            ->where('order_item.store_id IN (?)', $storeIds)
            ->where('TO_DAYS(NOW()) - TO_DAYS(order_item.created_at) <= ?', $period);

        $productMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $productSelect = $connection->select()->from(
            ['order_item' => $tableName],
            ['id' => $productIdField, 'cnt' => new \Zend_Db_Expr('COUNT(*)')]
        )
            ->join(
                ['order' => $this->resourceConnection->getTableName('sales_order')],
                'order_item.order_id = order.entity_id',
                []
            )
            ->joinLeft(
                ['configurable' => $this->resourceConnection->getTableName('catalog_product_super_link')],
                'order_item.parent_item_id IS NOT NULL AND order_item.product_id = configurable.product_id',
                []
            )
            ->joinLeft(
                ['bundle' => $this->resourceConnection->getTableName('catalog_product_bundle_selection')],
                'order_item.parent_item_id IS NOT NULL AND order_item.product_id = bundle.product_id',
                []
            )
            ->joinLeft(
                ['parent_product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                sprintf(
                    'parent_product.%1$s = bundle.parent_product_id
                    OR parent_product.%1$s = configurable.parent_id',
                    $productMetadata->getLinkField()
                ),
                []
            )
            ->where(sprintf('%s NOT IN(?)', $productIdField), $productIds)
            ->where('order.entity_id IN(?)', $orderSelect)
            ->where('order_item.store_id IN (?)', $storeIds)
            ->group('order_item.product_id')
            ->order('cnt DESC')
            ->limit(self::QUERY_LIMIT);

        if ($orderStatuses) {
            $productSelect->where('order.status IN (?)', $orderStatuses);
        }

        return $connection->fetchAll($productSelect);
    }
}
