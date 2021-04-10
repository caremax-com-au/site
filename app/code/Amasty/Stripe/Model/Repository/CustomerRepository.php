<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Model\Repository;

use Amasty\Stripe\Api\Data\CustomerInterface;
use Amasty\Stripe\Api\CustomerRepositoryInterface;
use Amasty\Stripe\Model\CustomerFactory;
use Amasty\Stripe\Model\ResourceModel\Customer as CustomerResource;
use Amasty\Stripe\Model\ResourceModel\Customer\CollectionFactory;
use Amasty\Stripe\Model\ResourceModel\Customer\Collection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerResource
     */
    private $customerResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $customers;

    /**
     * @var CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        CustomerFactory $customerFactory,
        CustomerResource $customerResource,
        CollectionFactory $customerCollectionFactory,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->customerFactory = $customerFactory;
        $this->customerResource = $customerResource;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function save(CustomerInterface $customer)
    {
        try {
            if ($customer->getEntityId()) {
                $customer = $this->getById($customer->getEntityId())->addData($customer->getData());
            }
            $this->customerResource->save($customer);
            unset($this->customers[$customer->getEntityId()]);
        } catch (\Exception $e) {
            if ($customer->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save customer with ID %1. Error: %2',
                        [$customer->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new customer. Error: %1', $e->getMessage()));
        }

        return $customer;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        /** @var \Amasty\Stripe\Model\Customer $customer */
        $customer = $this->customerFactory->create();
        $this->customerResource->load($customer, $entityId, CustomerInterface::ENTITY_ID);
        if (!$customer->getEntityId()) {
            throw new NoSuchEntityException(__('Customer with specified ID "%1" not found.', $entityId));
        }

        return $customer;
    }

    /**
     * @inheritdoc
     */
    public function delete(CustomerInterface $customer)
    {
        try {
            $this->customerResource->delete($customer);
            unset($this->customers[$customer->getEntityId()]);
        } catch (\Exception $e) {
            if ($customer->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove customer with ID %1. Error: %2',
                        [$customer->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove customer. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $customerModel = $this->getById($entityId);
        $this->delete($customerModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Stripe\Model\ResourceModel\Customer\Collection $customerCollection */
        $customerCollection = $this->customerCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $customerCollection);
        }

        $searchResults->setTotalCount($customerCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $customerCollection);
        }

        $customerCollection->setCurPage($searchCriteria->getCurrentPage());
        $customerCollection->setPageSize($searchCriteria->getPageSize());

        $customers = [];
        /** @var CustomerInterface $customer */
        foreach ($customerCollection->getItems() as $customer) {
            $customers[] = $this->getById($customer->getEntityId());
        }

        $searchResults->setItems($customers);

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getStripeCustomer($customerId, $accountId)
    {
        /** @var \Magento\Framework\Api\SearchCriteria $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilder->addFilter(CustomerInterface::CUSTOMER_ID, $customerId)
            ->addFilter(CustomerInterface::STRIPE_ACCOUNT_ID, $accountId)
            ->create();

        $accounts = $this->getList($criteriaBuilder)->getItems();
        if (!isset($accounts[0])) {
            throw new NoSuchEntityException(__('Customer with specified customer ID "%1" and stripe'
                . ' account ID "%2" not found.', $customerId, $accountId));
        }

        return $accounts[0];
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $customerCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $customerCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $customerCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $customerCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $customerCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $customerCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
