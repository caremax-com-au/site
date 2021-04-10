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



namespace Mirasvit\Feed\Export\Step;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Feed\Export\Context;
use Mirasvit\Feed\Repository\RuleRepository;

class Filtration extends AbstractStep
{
    private $ruleRepository;

    private $resource;

    private $stepFactory;

    public function __construct(
        RuleRepository $ruleRepository,
        ResourceConnection $resource,
        Context $context
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->resource       = $resource;
        $this->stepFactory    = $context->getStepFactory();

        parent::__construct($context);
    }

    /**
     * Add assigned rules as sub steps
     */
    public function beforeExecute()
    {
        if ($this->context->isTestMode()) {
            return parent::beforeExecute();
        }

        foreach ($this->context->getFeed()->getRuleIds() as $ruleId) {
            $rule = $this->ruleRepository->get($ruleId);

            $this->addStep(
                $this->stepFactory->create(Filtration\Rule::class, ['data' => ['rule_id' => $ruleId]])
                    ->setName($rule->getName())
            );
        }

        return parent::beforeExecute();
    }

    /**
     * Merge rules
     */
    public function afterExecute()
    {
        $feed = $this->context->getFeed();

        $connection = $this->resource->getConnection();
        $feedId     = (int)$feed->getId();

        $columns = [
            'product_id' => 'product_id',
            'feed_id'    => new \Zend_Db_Expr($feedId),
            'is_new'     => new \Zend_Db_Expr('1'),
        ];
        $select  = $connection->select();
        /** @var \Magento\Framework\DB\Select $select */
        $select->from(['main_table' => $this->resource->getTableName('mst_feed_rule_product')], $columns)
            ->group(['main_table.product_id'])
            ->where('main_table.rule_id IN (?)', $feed->getRuleIds())
            ->having('count(main_table.product_id) = ?', count($feed->getRuleIds()))
            ->useStraightJoin();

        $feedProductTable = $this->resource->getTableName('mst_feed_feed_product');

        $connection->delete($feedProductTable, ['feed_id = ' . intval($feedId)]);

        $insertQuery = $select->insertFromSelect($feedProductTable, array_keys($columns));
        $connection->query($insertQuery);
    }
}
