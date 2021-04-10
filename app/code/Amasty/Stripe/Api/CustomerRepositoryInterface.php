<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Api;

/**
 * @api
 */
interface CustomerRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Stripe\Api\Data\CustomerInterface $customer
     *
     * @return \Amasty\Stripe\Api\Data\CustomerInterface
     */
    public function save(\Amasty\Stripe\Api\Data\CustomerInterface $customer);

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return \Amasty\Stripe\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Delete
     *
     * @param \Amasty\Stripe\Api\Data\CustomerInterface $customer
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Stripe\Api\Data\CustomerInterface $customer);

    /**
     * Delete by id
     *
     * @param int $entityId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($entityId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Lists
     *
     * @param int $customerId
     * @param string $accountId
     *
     * @return \Amasty\Stripe\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStripeCustomer($customerId, $accountId);
}
