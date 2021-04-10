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


declare(strict_types=1);

namespace Mirasvit\Feed\Ui\Rule\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Feed\Repository\RuleRepository;

class DataProvider extends AbstractDataProvider
{
    private $ruleRepository;

    /**
     * @param RuleRepository $ruleRepository
     * @param string         $name
     * @param string         $primaryFieldName
     * @param string         $requestFieldName
     * @param array          $meta
     * @param array          $data
     */
    public function __construct(
        RuleRepository $ruleRepository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->ruleRepository = $ruleRepository;

        $this->collection = $this->ruleRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        $result = [];
        foreach ($this->collection as $model) {
            $data = $model->getData();;

            $data['feed_ids'] = $this->ruleRepository->getFeedIds($model);

            $result[$model->getId()] = $data;
        }

        return $result;
    }
}
