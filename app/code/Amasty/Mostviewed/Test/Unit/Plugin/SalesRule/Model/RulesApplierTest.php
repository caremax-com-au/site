<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Test\Unit\Plugin\SalesRule\Model;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Plugin\SalesRule\Model\RulesApplier;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Framework\DataObject;

/**
 * Class RulesApplierTest
 *
 * @see RulesApplier
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class RulesApplierTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers RulesApplier::checkForParents
     *
     * @dataProvider checkForParentsDataProvider
     *
     * @throws \ReflectionException
     */
    public function testCheckForParents($productsInCart, $packsData, $productId, $storeId, $productsQty, $expectedResult)
    {
        /** @var RulesApplier|\PHPUnit\Framework\MockObject\MockObject $plugin */
        $plugin = $this->createPartialMock(RulesApplier::class, [
            'getProductsInCart'
        ]);
        $plugin->expects($this->any())->method('getProductsInCart')->willReturn($productsInCart);
        $packs = [];
        foreach ($packsData as $packData) {
            $packs[] = $this->getObjectManager()->getObject(\Amasty\Mostviewed\Model\Pack::class, [
                'data' => $packData,
                'productQtyAssociations' => [1 => 23]
            ]);
        }

        $priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $priceCurrency->expects($this->any())->method('convert')->willReturnArgument(0);

        $packRepository = $this->createMock(\Amasty\Mostviewed\Api\PackRepositoryInterface::class);
        $packRepository->expects($this->any())->method('getPacksByParentProductsAndStore')->willReturn($packs);

        $checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $session = $this->createMock(\Magento\Customer\Model\Session::class);
        $session->expects($this->any())->method('getCustomerGroupId')->willReturn(0);

        $quoteItem = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, [
            'getProductId',
            'getStoreId',
            'getQuote'
        ]);
        $quote = $this->getObjectManager()->getObject(DataObject::class, [
            'data' => ['store' => $storeId]
        ]);
        $quoteItem->expects($this->any())->method('getProductId')->willReturn($productId);
        $quoteItem->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $quoteItem->expects($this->any())->method('getQuote')->willReturn($quote);

        $this->setProperty($plugin, 'packRepository', $packRepository, RulesApplier::class);
        $this->setProperty($plugin, 'productsQty', $productsQty, RulesApplier::class);
        $this->setProperty($plugin, 'priceCurrency', $priceCurrency, RulesApplier::class);
        $this->setProperty($plugin, 'customerSession', $session, RulesApplier::class);
        $this->setProperty($plugin, 'checkoutSession', $checkoutSession, RulesApplier::class);
        $plugin->setItem($quoteItem);
        $plugin->setItemData([
            'itemPrice' => 10,
            'baseItemPrice' => 9,
            'itemOriginalPrice' => 8,
            'baseOriginalPrice' => 7
        ]);

        $this->assertEquals($expectedResult, $this->invokeMethod($plugin, 'checkForParents'));
    }

    /**
     * @covers RulesApplier::checkForChilds
     *
     * @dataProvider checkForChildsDataProvider
     *
     * @throws \ReflectionException
     */
    public function testCheckForChilds($productsInCart, $packsData, $productId, $storeId, $productsQty, $expectedResult)
    {
        /** @var RulesApplier|\PHPUnit\Framework\MockObject\MockObject $plugin */
        $plugin = $this->createPartialMock(RulesApplier::class, [
            'getProductsInCart'
        ]);
        $plugin->expects($this->any())->method('getProductsInCart')->willReturn($productsInCart);
        $packs = [];
        foreach ($packsData as $packData) {
            $packs[] = $this->getObjectManager()->getObject(\Amasty\Mostviewed\Model\Pack::class, [
                'data' => $packData,
                'productQtyAssociations' => [1 => 23]
            ]);
        }

        $priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $priceCurrency->expects($this->any())->method('convert')->willReturnArgument(0);

        $packRepository = $this->createMock(\Amasty\Mostviewed\Api\PackRepositoryInterface::class);
        $packRepository->expects($this->any())->method('getPacksByChildProductsAndStore')->willReturn($packs);

        $checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $session = $this->createMock(\Magento\Customer\Model\Session::class);
        $session->expects($this->any())->method('getCustomerGroupId')->willReturn(0);

        $quoteItem = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, [
            'getProductId',
            'getStoreId',
            'getQuote'
        ]);
        $quote = $this->getObjectManager()->getObject(DataObject::class, [
            'data' => ['store' => $storeId]
        ]);
        $quoteItem->expects($this->any())->method('getProductId')->willReturn($productId);
        $quoteItem->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $quoteItem->expects($this->any())->method('getQuote')->willReturn($quote);

        $this->setProperty($plugin, 'packRepository', $packRepository, RulesApplier::class);
        $this->setProperty($plugin, 'productsQty', $productsQty, RulesApplier::class);
        $this->setProperty($plugin, 'priceCurrency', $priceCurrency, RulesApplier::class);
        $this->setProperty($plugin, 'customerSession', $session, RulesApplier::class);
        $this->setProperty($plugin, 'checkoutSession', $checkoutSession, RulesApplier::class);
        $plugin->setItem($quoteItem);
        $plugin->setItemData([
            'itemPrice' => 10,
            'baseItemPrice' => 9,
            'itemOriginalPrice' => 8,
            'baseOriginalPrice' => 7
        ]);

        $this->assertEquals($expectedResult, $this->invokeMethod($plugin, 'checkForChilds'));
    }

    /**
     * @covers RulesApplier::applyPackRule
     *
     * @dataProvider applyPackRuleDataProvider
     *
     * @throws \ReflectionException
     */
    public function testApplyPackRule($itemData, $packData, $totalQty, $countCanApplied, $minQty, $expectedResult)
    {
        /** @var RulesApplier|\PHPUnit\Framework\MockObject\MockObject $plugin */
        $plugin = $this->getObjectManager()->getObject(RulesApplier::class);

        $pack = $this->getObjectManager()->getObject(\Amasty\Mostviewed\Model\Pack::class, [
            'data' => $packData
        ]);

        $priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $priceCurrency->expects($this->any())->method('convert')->willReturnArgument(0);
        $priceCurrency->expects($this->any())->method('round')->willReturnArgument(0);

        $quoteItem = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, [
            'getTotalQty',
            'getQuote'
        ]);
        $quote = $this->getObjectManager()->getObject(DataObject::class, [
            'data' => ['store' => 1]
        ]);
        $quoteItem->expects($this->any())->method('getTotalQty')->willReturn($totalQty);
        $quoteItem->expects($this->any())->method('getQuote')->willReturn($quote);
        $this->setProperty($plugin, 'priceCurrency', $priceCurrency, RulesApplier::class);
        $this->setProperty($plugin, 'itemData', $itemData, RulesApplier::class);
        $plugin->setItem($quoteItem);

        $this->invokeMethod($plugin, 'applyPackRule', [$pack, $countCanApplied, $minQty]);

        $this->assertEquals($expectedResult, $plugin->getItem()->getDiscountAmount());
    }

    /**
     * Data provider for applyPackRule test
     * @return array
     */
    public function applyPackRuleDataProvider()
    {
        return [
            [
                [
                    'itemPrice' => 30,
                    'baseItemPrice' => 30
                ],
                [
                    PackInterface::DISCOUNT_TYPE => DiscountType::FIXED,
                    PackInterface::DISCOUNT_AMOUNT => 10
                ],
                5,
                3,
                2,
                20
            ],
            [
                [
                    'itemPrice' => 30,
                    'baseItemPrice' => 30
                ],
                [
                    PackInterface::DISCOUNT_TYPE => DiscountType::FIXED,
                    PackInterface::DISCOUNT_AMOUNT => 40
                ],
                5,
                3,
                2,
                60
            ],
            [
                [
                    'itemPrice' => 30,
                    'baseItemPrice' => 30
                ],
                [
                    PackInterface::DISCOUNT_TYPE => DiscountType::FIXED,
                    PackInterface::DISCOUNT_AMOUNT => 40
                ],
                5,
                2,
                3,
                0
            ],
            [
                [
                    'itemPrice' => 30,
                    'baseItemPrice' => 30
                ],
                [
                    PackInterface::DISCOUNT_TYPE => DiscountType::FIXED,
                    PackInterface::DISCOUNT_AMOUNT => 10
                ],
                5,
                10,
                2,
                40
            ],
            [
                [
                    'itemPrice' => 30,
                    'baseItemPrice' => 30
                ],
                [
                    PackInterface::DISCOUNT_TYPE => DiscountType::PERCENTAGE,
                    PackInterface::DISCOUNT_AMOUNT => 10
                ],
                5,
                10,
                2,
                12
            ]
        ];
    }

    /**
     * Data provider for checkForParents test
     * @return array
     */
    public function checkForParentsDataProvider()
    {
        return [
            [
                [3, 5],
                [
                    ['apply_for_parent' => true, 'product_ids' => '2,3']
                ],
                1,
                1,
                [3 => 1, 5 => 1],
                true
            ],
            [
                [3, 5],
                [
                    ['apply_for_parent' => false, 'product_ids' => '2,3'],
                    ['apply_for_parent' => false, 'product_ids' => '2,4']
                ],
                1,
                1,
                [3 => 1, 5 => 1],
                false
            ],
            [
                [3, 5],
                [
                    ['apply_for_parent' => true, 'product_ids' => '2,3'],
                    ['apply_for_parent' => false, 'product_ids' => '2,4']
                ],
                3,
                1,
                [3 => 1, 5 => 1],
                false
            ],
            [
                [3, 5],
                [
                    ['apply_for_parent' => false, 'product_ids' => '2,3'],
                    ['apply_for_parent' => true, 'product_ids' => '2,4']
                ],
                3,
                1,
                [3 => 1, 5 => 1],
                false
            ]
        ];
    }

    /**
     * Data provider for checkForChilds test
     * @return array
     */
    public function checkForChildsDataProvider()
    {
        return [
            [
                [3, 5],
                [
                    ['parent_ids' => [2,3]]
                ],
                1,
                1,
                [3 => 1, 5 => 1],
                true
            ],
            [
                [3, 5],
                [
                    ['parent_ids' => [2,4]]
                ],
                3,
                1,
                [3 => 1, 5 => 1],
                false
            ],
            [
                [3, 5],
                [
                    ['parent_ids' => [2,3]],
                    ['parent_ids' => [2,4]]
                ],
                3,
                1,
                [3 => 1, 5 => 1],
                false
            ]
        ];
    }
}
