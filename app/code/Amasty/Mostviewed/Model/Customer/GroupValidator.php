<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Customer;

use Magento\Customer\Api\Data\GroupInterface as CustomerGroupInterface;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\Model\AbstractModel;

class GroupValidator
{
    /**
     * @var CustomerSessionFactory
     */
    private $customerSessionFactory;

    public function __construct(CustomerSessionFactory $customerSessionFactory)
    {
        $this->customerSessionFactory = $customerSessionFactory;
    }

    public function validate(AbstractModel $entity): bool
    {
        if (!method_exists($entity, 'getCustomerGroupIds')) {
            return false;
        }

        $currentCustomerGroup = $this->customerSessionFactory->create()->getCustomerGroupId()
            ?: GroupManagement::NOT_LOGGED_IN_ID;
        $customerGroups = $entity->getCustomerGroupIds();
        $customerGroups = explode(',', $customerGroups);

        return in_array($currentCustomerGroup, $customerGroups)
            || in_array(CustomerGroupInterface::CUST_GROUP_ALL, $customerGroups);
    }
}
