<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Ui\DataProvider\History;

use Amasty\Acart\Model\ResourceModel\History\CollectionFactory;

class HistoryDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Amasty\Acart\Model\ResourceModel\History\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    protected $collectionInitialized = false;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * @return \Amasty\Acart\Model\ResourceModel\History\Collection
     */
    public function getCollection()
    {
        /** @var \Amasty\Acart\Model\ResourceModel\History\Collection $collection */
        $collection = parent::getCollection();

        if (!$this->collectionInitialized) {
            $collection->addRuleQuoteData()
                ->addRuleData();

            $collection->addFieldToFilter(
                [
                    'ruleQuote' => 'ruleQuote.status',
                    'history' => 'main_table.status'
                ],
                [
                    'ruleQuote' => ['neq' => \Amasty\Acart\Model\RuleQuote::STATUS_PROCESSING],
                    'history' => ['neq' => \Amasty\Acart\Model\History::STATUS_PROCESSING]
                ]
            );

            $this->collectionInitialized = true;
        }

        return $collection;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $collection = $this->getCollection();
        if (!$collection->isLoaded()) {
            $collection->load();
        }
        $items = $collection->toArray();

        return [
            'totalRecords' => $collection->getSize(),
            'items' => array_values($items['items']),
        ];
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     *
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }
}
