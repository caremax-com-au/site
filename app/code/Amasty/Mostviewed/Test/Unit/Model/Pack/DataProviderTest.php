<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Model\Pack;

use Amasty\Mostviewed\Api\Data\PackExtensionInterface;
use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Model\Pack\DataProvider as DataProviderModel;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProviderTest
 *
 * @see DataProviderModel
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var DataProviderModel
     */
    private $model;

    protected function setup(): void
    {
        $collection = $this->createMock(\Amasty\Mostviewed\Model\ResourceModel\Pack\Collection::class);
        $coreRegistry = $this->createMock(\Magento\Framework\Registry::class);
        $pack = $this->createMock(Pack::class);
        $extensionAttributes = $this->createMock(PackExtensionInterface::class);
        $dataPersistor = $this->createMock(DataPersistorInterface::class);
        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)
            ->setMethods(['create', 'addIdFilter', 'addAttributeToSelect', 'getItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $coreRegistry->expects($this->any())->method('registry')->willReturnOnConsecutiveCalls(null, $pack, $pack);
        $pack->expects($this->any())->method('getPackId')->willReturn(1);
        $pack->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->any())->method('getStores')->willReturn(null);
        $dataPersistor->expects($this->any())->method('get')->willReturnOnConsecutiveCalls(null, $pack);
        $productCollection->expects($this->any())->method('create')->willReturn($productCollection);
        $productCollection->expects($this->any())->method('addIdFilter')->willReturn($productCollection);
        $productCollection->expects($this->any())->method('addAttributeToSelect')->willReturn($productCollection);
        $productCollection->expects($this->any())->method('getItems')->willReturn([]);

        $this->model = $this->getObjectManager()->getObject(
            DataProviderModel::class,
            [
                'collection' => $collection,
                'coreRegistry' => $coreRegistry,
                'productCollectionFactory' => $productCollection,
            ]
        );
    }

    /**
     * @covers DataProviderModel::getData
     */
    public function testGetData()
    {
        $this->assertNull($this->model->getData());
        $this->assertEquals([1 => []], $this->model->getData());
        $this->assertEquals([1 => []], $this->model->getData());
    }

    /**
     * @covers DataProviderModel::convertProductsData
     *
     * @dataProvider convertProductsDataDataProvider
     *
     * @param array $packData
     * @param array $expectedResult
     * @return void
     * @throws \ReflectionException
     */
    public function testConvertProductsData(array $packData, array $expectedResult): void
    {
        $pack = $this->createMock(Pack::class);
        $extensionAttributes = $this->createMock(PackExtensionInterface::class);
        $pack->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->any())->method('getStores')->willReturn(null);
        $pack->expects($this->any())->method('getData')->willReturn($packData);

        $this->assertEquals(
            $expectedResult,
            $this->invokeMethod($this->model, 'convertProductsData', [$pack])
        );
    }

    public function convertProductsDataDataProvider(): array
    {
        return [
            [
                [], []
            ],
            [
                ['product_ids' => 'test'],
                ['product_ids' =>['child_products_container' => []]],
            ],
            [
                ['parent_ids' => 'test'],
                ['parent_ids' =>['parent_products_container' => []]]
            ]
        ];
    }
}
