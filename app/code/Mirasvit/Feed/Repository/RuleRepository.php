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



namespace Mirasvit\Feed\Repository;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityManager;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\Feed\Api\Data\RuleInterface;
use Mirasvit\Feed\Api\Data\RuleInterfaceFactory;
use Mirasvit\Feed\Model\ResourceModel\Rule\CollectionFactory;
use Mirasvit\Feed\Model\Rule\RuleFactory;

class RuleRepository
{
    private $factory;

    private $collectionFactory;

    private $entityManager;

    private $ruleFactory;

    private $resource;

    public function __construct(
        RuleInterfaceFactory $factory,
        CollectionFactory $collectionFactory,
        EntityManager $entityManager,
        RuleFactory $ruleFactory,
        ResourceConnection $resource
    ) {
        $this->factory           = $factory;
        $this->collectionFactory = $collectionFactory;
        $this->entityManager     = $entityManager;
        $this->ruleFactory       = $ruleFactory;
        $this->resource          = $resource;
    }

    /**
     * @return RuleInterface[]|\Mirasvit\Feed\Model\ResourceModel\Rule\Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return RuleInterface
     */
    public function create()
    {
        return $this->factory->create();
    }

    /**
     * @param int $id
     *
     * @return RuleInterface|null
     */
    public function get(int $id)
    {
        $model = $this->create();
        $model = $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function save(RuleInterface $model): RuleInterface
    {
        return $this->entityManager->save($model);
    }

    public function delete(RuleInterface $model)
    {
        $this->entityManager->delete($model);
    }

    /**
     * @return \Mirasvit\Feed\Model\Rule\Rule
     */
    public function createRuleInstance()
    {
        return $this->ruleFactory->create();
    }

    /**
     * @param RuleInterface $model
     *
     * @return \Mirasvit\Feed\Model\Rule\Rule
     */
    public function getRuleInstance(RuleInterface $model)
    {
        $rule = $this->createRuleInstance();
        $rule->getConditions()->loadArray(
            SerializeService::decode($model->getConditionsSerialized())
        );

        return $rule;
    }

    public function getFeedIds(RuleInterface $model): array
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from($this->resource->getTableName(RuleInterface::REL_FEED_TABLE_NAME), ['feed_id'])
            ->where('rule_id = :rule_id');

        return $connection->fetchCol($select, [':rule_id' => $model->getId()]);
    }

    public function saveFeedIds(RuleInterface $model, array $feedIds)
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(RuleInterface::REL_FEED_TABLE_NAME);

        $connection->delete($table, ['rule_id = ?' => $model->getId()]);

        foreach ($feedIds as $feedId) {
            $connection->insert($table, [
                'rule_id' => $model->getId(),
                'feed_id' => $feedId,
            ]);
        }
    }

    public function getProductIds(RuleInterface $model): array
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from($this->resource->getTableName(RuleInterface::REL_PRODUCT_TABLE_NAME), ['feed_id'])
            ->where('rule_id = :rule_id');

        return $connection->fetchCol($select, [':rule_id' => $model->getId()]);
    }

    public function clearProductIds(RuleInterface $model)
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(RuleInterface::REL_PRODUCT_TABLE_NAME);

        $connection->delete($table, ['rule_id = ?' => $model->getId()]);
    }

    public function addProductIds(RuleInterface $model, array $productIds)
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(RuleInterface::REL_PRODUCT_TABLE_NAME);

        foreach ($productIds as $productId) {
            $connection->insert($table, [
                'rule_id'    => $model->getId(),
                'product_id' => $productId,
            ]);
        }
    }
}
