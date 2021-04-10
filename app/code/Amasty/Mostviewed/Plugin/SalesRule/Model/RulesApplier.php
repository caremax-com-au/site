<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Plugin\SalesRule\Model;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class RulesApplier
{
    /**
     * @var AbstractItem
     */
    private $item;

    /**
     * @var array
     */
    protected $itemData;

    /**
     * @var \Magento\SalesRule\Model\Validator
     */
    private $validator;

    /**
     * @var \Amasty\Mostviewed\Api\PackRepositoryInterface
     */
    protected $packRepository;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var Pack\Cart\Discount\CalculateDiscount
     */
    private $calculateDiscount;

    public function __construct(
        \Amasty\Mostviewed\Api\PackRepositoryInterface $packRepository,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Amasty\Mostviewed\Model\Pack\Cart\Discount\CalculateDiscount $calculateDiscount
    ) {
        $this->packRepository = $packRepository;
        $this->validator = $validator;
        $this->priceCurrency = $priceCurrency;
        $this->checkoutSession = $checkoutSession;
        $this->clearAppliedPackIds();
        $this->calculateDiscount = $calculateDiscount;
    }

    /**
     * @param \Magento\SalesRule\Model\RulesApplier $subject
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rules
     * @param bool $skipValidation
     * @param mixed $couponCode
     *
     * @return array
     */
    public function beforeApplyRules($subject, $item, $rules, $skipValidation, $couponCode)
    {
        $this->setItem($item);
        $this->itemData = [
            'itemPrice' => $this->validator->getItemPrice($item),
            'baseItemPrice' => $this->validator->getItemBasePrice($item),
            'itemOriginalPrice' => $this->validator->getItemOriginalPrice($item),
            'baseOriginalPrice' => $this->validator->getItemBaseOriginalPrice($item)
        ];

        return [$item, $rules, $skipValidation, $couponCode];
    }

    public function afterApplyRules(\Magento\SalesRule\Model\RulesApplier $subject, array $appliedRuleIds): array
    {
        $appliedPacks = $this->calculateDiscount->execute($this->getItem());
        foreach ($appliedPacks as $packId => $itemQty) {
            $this->applyPackRule($this->packRepository->getById($packId), $itemQty);
            $this->saveAppliedPackId($packId);
        }

        if ($appliedPacks) {
            $appliedRuleIds = [];
        }

        return $appliedRuleIds;
    }

    private function applyPackRule(PackInterface $pack, float $qty): void
    {
        $amount = 0;
        $baseAmount = 0;

        $discountAmount = $pack->getChildProductDiscount((int) $this->getItem()->getProduct()->getId())
            ?? $pack->getDiscountAmount();

        switch ($pack->getDiscountType()) {
            case DiscountType::FIXED:
                $amount = $qty * $this->priceCurrency->convert(
                    $discountAmount,
                    $this->getItem()->getQuote()->getStore()
                );
                $baseAmount = $qty * $discountAmount;
                break;
            case DiscountType::PERCENTAGE:
                $amount = $qty * $this->itemData['itemPrice'] * $discountAmount / 100;
                $baseAmount = $qty * $this->itemData['baseItemPrice'] * $discountAmount / 100;
                $amount = $this->priceCurrency->round($amount);
                $baseAmount = $this->priceCurrency->round($baseAmount);
                break;
        }

        $amount = min($amount, $qty * $this->itemData['itemPrice'])
            + $this->getItem()->getAmDiscountAmount() ?: 0;
        $baseAmount = min($baseAmount, $qty * $this->itemData['baseItemPrice'])
            + $this->getItem()->getAmBaseDiscountAmount() ?: 0;

        if ($baseAmount) {
            $this->getItem()->setDiscountAmount($amount);
            $this->getItem()->setAmDiscountAmount($amount);
            $this->getItem()->setBaseDiscountAmount($baseAmount);
            $this->getItem()->setAmBaseDiscountAmount($baseAmount);
        }
    }

    public function setItem(AbstractItem $item): void
    {
        $this->item = $item;
    }

    public function getItem(): AbstractItem
    {
        return $this->item;
    }

    private function saveAppliedPackId(int $packId): void
    {
        $bundlePackIds = $this->checkoutSession->getAppliedPackIds() ?: [];
        if (!in_array($packId, $bundlePackIds)) {
            $bundlePackIds[] = $packId;
        }
        $this->checkoutSession->setAppliedPackIds($bundlePackIds);
    }

    private function clearAppliedPackIds()
    {
        $this->checkoutSession->setAppliedPackIds([]);
    }

    public function setItemData(array $itemData): void
    {
        $this->itemData = $itemData;
    }
}
