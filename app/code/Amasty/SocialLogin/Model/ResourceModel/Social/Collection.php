<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\ResourceModel\Social;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Amasty\SocialLogin\Model\Social::class, \Amasty\SocialLogin\Model\ResourceModel\Social::class);
    }

    /**
     * @param int $websiteId
     *
     * @return $this
     */
    public function addWebsiteFilter($websiteId)
    {
        $this->getSelect()
            ->join(
                ['customer' => $this->getTable('customer_entity')],
                'customer.entity_id = main_table.customer_id AND customer.website_id=' . $websiteId,
                []
            );
        return $this;
    }
}
