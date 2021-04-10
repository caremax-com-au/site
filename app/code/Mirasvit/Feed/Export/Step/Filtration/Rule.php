<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.30
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Export\Step\Filtration;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Mirasvit\Feed\Export\Context;
use Mirasvit\Feed\Export\Step\AbstractStep;
use Mirasvit\Feed\Repository\RuleRepository;

class Rule extends AbstractStep
{
    private $ruleRepository;

    private $productCollectionFactory;

    private $rule;

    private $ruleInstance;

    public function __construct(
        RuleRepository $ruleRepository,
        ProductCollectionFactory $productCollectionFactory,
        Context $context,
        array $data = []
    ) {
        $this->ruleRepository           = $ruleRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->context                  = $context;

        $ruleId = (int)$data['rule_id'];

        $this->rule = $this->ruleRepository->get($ruleId);
        if ($this->rule) {
            $this->ruleInstance = $this->ruleRepository->getRuleInstance($this->rule);
        }

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        parent::beforeExecute();

        $this->index  = 0;
        $this->length = $this->getProductCollection()->getSize();

        $this->ruleRepository->clearProductIds($this->rule);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->isReady()) {
            $this->beforeExecute();
        }

        $validIds = [];

        $lastId = 0;
        while (!$this->isCompleted()) {
            $collection = $this->getProductCollection();

            $collection->getSelect()->group('e.entity_id');

            if ($lastId) {
                $collection->getSelect()
                    ->where('e.entity_id > ?', $lastId)
                    ->limit(100, 0);
            } else {
                $collection->getSelect()
                    ->limit(100, $this->index);
            }

            $startIndex = $this->index;

            foreach ($collection as $product) {
                $lastId = (int)$product->getId();
                $this->index++;

                if ($this->ruleInstance->getConditions()->validate($product)) {
                    $validIds[] = $product->getId();
                }

                if ($this->context->isTimeout()) {
                    break 2;
                }
            }

            #sometimes collection getSize not equal real number of items
            if ($startIndex == $this->index) {
                $this->length = $this->index;
            }
        }

        $this->ruleRepository->addProductIds($this->rule, $validIds);

        if ($this->isCompleted()) {
            $this->afterExecute();
        }
    }

    /**
     * Product collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create()
            ->addStoreFilter($this->context->getFeed()->getStoreId())
            ->setStoreId($this->context->getFeed()->getStoreId())
            ->setFlag('has_stock_status_filter', true);

        $collection->getSelect()->order('e.entity_id asc');

        // add base attributes - improve simple filters time
        $collection->addAttributeToSelect('status')
            ->addAttributeToSelect('visibility');

        // fast filtering mode
        if ($this->context->getFeed()->getFilterFastmodeEnabled()) {
            $this->ruleInstance->getConditions()->applyConditions($collection);
        }

        // to avoid conflict with the module Sorting
        $collection->setFlag('NO_SORT', true);

        return $collection;
    }
}
