<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */

namespace Amasty\Stripe\Model\Quote;

use Magento\Checkout\Block\Cart\Totals as TotalsBlock;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

class Totals
{

    /**
     * @var Totals
     */
    private $totals;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        TotalsBlock $totals
    ) {
        $this->totals = $totals;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param Quote $quote
     *
     * @return array
     */
    public function getTotals(Quote $quote)
    {
        $quote->setCollectShippingRates(true)
            ->setTotalsCollectedFlag(false)
            ->collectTotals();

        $totals = [];
        $this->totals->setCustomQuote($quote);
        foreach ($this->totals->getTotals() as $total) {
            if ($total->getValue() || in_array($total->getCode(), ['shipping', 'grand_total'])) {
                $totals[] = [
                    'title' => $total->getTitle(),
                    'value' => $total->getValue(),
                    'code'  => $total->getCode(),
                ];
            }
        }

        return $totals;
    }
}
