<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Plugin\Adminhtml\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;
use Amasty\Stripe\Model\Ui\ConfigProvider;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order\Config as OrderConfig;

class ShipmentSavePlugin
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

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        InvoiceRepositoryInterface $invoiceRepository,
        OrderResource $orderResource,
        OrderConfig $orderConfig
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderResource = $orderResource;
        $this->orderConfig = $orderConfig;
    }

    /**
     * @param Save $subject
     * @param ResultInterface $result
     *
     * @return ResultInterface
     */
    public function afterExecute(Save $subject, $result)
    {
        $orderId = $subject->getRequest()->getParam(OrderItemInterface::ORDER_ID);
        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);

        if ($order->getPayment()->getMethod() === ConfigProvider::CODE) {
            $this->searchCriteriaBuilder->addFilter(OrderItemInterface::ORDER_ID, $orderId);
            /** @var InvoiceCollection $invoiceList */
            $invoiceList = $this->invoiceRepository->getList($this->searchCriteriaBuilder->create());

            if ($invoiceList->getSize()) {
                $order->setState(Order::STATE_COMPLETE)
                    ->setStatus($this->orderConfig->getStateDefaultStatus($order->getState()));
                $this->orderResource->save($order);
            }
        }

        return $result;
    }
}
