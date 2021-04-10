<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Model\ResourceModel;

use Amasty\Stripe\Api\Data\CustomerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Customer extends AbstractDb
{
    const TABLE_NAME = 'amasty_stripe_customers';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, CustomerInterface::ENTITY_ID);
    }
}
