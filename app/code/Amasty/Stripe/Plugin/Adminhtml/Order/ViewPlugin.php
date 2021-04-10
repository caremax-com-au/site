<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Plugin\Adminhtml\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\View;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;
use Amasty\Stripe\Model\Ui\ConfigProvider;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;

class ViewPlugin
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @param View $subject
     *
     * @return null
     */
    public function beforeExecute(View $subject)
    {
        $orderId = $subject->getRequest()->getParam(OrderItemInterface::ORDER_ID);
        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);

        if ($order->getPayment()->getMethod() === ConfigProvider::CODE) {
            $this->searchCriteriaBuilder->addFilter(OrderItemInterface::ORDER_ID, $orderId);
            /** @var InvoiceCollection $invoiceList */
            $invoiceList = $this->invoiceRepository->getList($this->searchCriteriaBuilder->create());

            if ($invoiceList->getSize()) {
                $order->setActionFlag(Order::ACTION_FLAG_INVOICE, false);
            }
        }

        return null;
    }
}
