<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\ResourceModel\Analytics;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Amasty\Mostviewed\Api\Data\AnalyticInterface;

class Analytic extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AnalyticInterface::MAIN_TABLE, AnalyticInterface::ID);
    }
}
