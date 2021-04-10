<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Model;

use Amasty\Stripe\Api\Data\CustomerInterface;
use Amasty\Stripe\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\Model\AbstractModel;

class Customer extends AbstractModel implements CustomerInterface
{
    public function _construct()
    {
        $this->_init(CustomerResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(CustomerInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(CustomerInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStripeCustomerId()
    {
        return $this->_getData(CustomerInterface::STRIPE_CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStripeCustomerId($stripeCustomerId)
    {
        $this->setData(CustomerInterface::STRIPE_CUSTOMER_ID, $stripeCustomerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStripeAccountId()
    {
        return $this->_getData(CustomerInterface::STRIPE_ACCOUNT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setAccountCustomerId($stripeAccountId)
    {
        $this->setData(CustomerInterface::STRIPE_ACCOUNT_ID, $stripeAccountId);

        return $this;
    }
}
