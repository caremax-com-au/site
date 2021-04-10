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



namespace Mirasvit\Feed\Model\Rule;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;

class Rule extends AbstractModel
{
    const FORM_NAME = 'mst_feed_rule_form';

    private $combineFactory;

    public function __construct(
        Condition\CombineFactory $combineFactory,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate
    ) {
        $this->combineFactory = $combineFactory;

        parent::__construct($context, $registry, $formFactory, $localeDate);
    }

    public function getActionsInstance()
    {
    }

    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    public function getMatchedProductIds(Collection $collection)
    {
        $this->getConditions()->applyConditions($collection);

        $ids = [];
        foreach ($collection as $item) {
            $ids[] = $item->getId();
        }

        return $ids;
    }
}
