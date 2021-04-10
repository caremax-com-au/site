<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Observer\Frontend;

use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\Pack\Analytic\AppendPackSales;
use Amasty\Mostviewed\Model\Pack\Cart\Discount\GetAppliedPacks;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

class PlaceOrderAfter implements ObserverInterface
{
    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var AppendPackSales
     */
    private $appendPackSales;

    /**
     * @var GetAppliedPacks
     */
    private $getAppliedPacks;

    public function __construct(
        PackRepositoryInterface $packRepository,
        AppendPackSales $appendPackSales,
        GetAppliedPacks $getAppliedPacks
    ) {
        $this->packRepository = $packRepository;
        $this->appendPackSales = $appendPackSales;
        $this->getAppliedPacks = $getAppliedPacks;
    }

    public function execute(Observer $observer)
    {
        /** @var CartInterface $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();

        if ($quote && $order) {
            $data = [];
            $appliedPacks = $this->getAppliedPacks->execute($quote);
            foreach ($appliedPacks as $appliedPack) {
                try {
                    $pack = $this->packRepository->getById(
                        $appliedPack->getPackId()
                    ); // packs already load in RulesApplier
                    $data[$pack->getPackId()] = [
                        'name' => $pack->getName(),
                        'qty' => $appliedPack->getPackQty()
                    ];
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            }

            $this->appendPackSales->execute((int) $order->getEntityId(), $data);
        }
    }
}
