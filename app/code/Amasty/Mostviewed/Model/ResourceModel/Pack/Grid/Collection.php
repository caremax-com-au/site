<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Grid;

use Amasty\Mostviewed\Model\Pack\Store\Table as StoreTable;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\GetAggregatedTable as GetAggregatedAnalyticTable;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Collection as PackCollection;

class Collection extends PackCollection implements SearchResultInterface
{
    /**
     * @var array
     */
    protected $_map = [
        'fields' => [
            'pack_id' => 'main_table.pack_id'
        ]
    ];

    /**
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * @var GetAggregatedAnalyticTable
     */
    private $getAggregatedAnalyticTable;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        GetAggregatedAnalyticTable $getAggregatedAnalyticTable,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        $this->getAggregatedAnalyticTable = $getAggregatedAnalyticTable;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    protected function _renderFiltersBefore()
    {
        $storesTable = $this->getConnection()->select()->from(
            $this->getTable(StoreTable::NAME),
            ['group_concat(store_id separator \',\') as stores', 'pack_entity_id' => 'pack_id']
        )->group('pack_entity_id');
        $this->getSelect()->join(
            ['stores' => $storesTable],
            'main_table.pack_id = stores.pack_entity_id',
            ['stores']
        );

        $this->getSelect()->joinLeft(
            ['sales_analytic' => $this->getAggregatedAnalyticTable->execute()],
            'main_table.pack_id = sales_analytic.pack_id',
            [GetAggregatedAnalyticTable::COUNT_COLUMN]
        );

        parent::_renderFiltersBefore();
    }
}
