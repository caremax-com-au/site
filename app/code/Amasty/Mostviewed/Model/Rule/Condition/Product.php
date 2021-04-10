<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types = 1);

namespace Amasty\Mostviewed\Model\Rule\Condition;

use Amasty\Mostviewed\Helper\Config as ConfigHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    public function apply(Collection $collection, ProductModel $product, bool $inverse): bool
    {
        $attributeCode = $this->getAttribute();
        $equals = $this->getOperator() === '==';
        $equals = $inverse ? !$equals : $equals;

        return $attributeCode == 'category_ids'
            ? $this->addCategoryFilter($collection, $product, $equals)
            : $this->addAttributeFilter($collection, $product, $attributeCode, $equals);
    }

    /**
     * @param Collection $collection
     * @param ProductModel $product
     * @param string $attributeCode
     * @param bool $equals
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addAttributeFilter(
        Collection $collection,
        ProductModel $product,
        string $attributeCode,
        bool $equals
    ) {
        $result = false;
        $attribute = $this->getAttributeObject();

        if ($attribute->getId() || $attributeCode == ProductInterface::ATTRIBUTE_SET_ID) {
            $attributeValue = $product->getData($attributeCode);

            if ($attributeValue === null) {
                $attributeValue = $this->getAttributeValue($product, $attributeCode) ?: '';
            }

            if ($this->isAttributeMultiselect($product, $attributeCode)) {
                $this->applyMultiselectAttribute($collection, $attributeValue, $attributeCode, $equals);
            } else {
                $collection->addAttributeToFilter(
                    $attributeCode,
                    [$equals ? 'eq' : 'neq' => $attributeValue]
                );
            }

            $result = true;
        }

        return $result;
    }

    /**
     * @param $collection
     * @param $attributeValue
     * @param $attributeCode
     * @param $equals
     */
    private function applyMultiselectAttribute($collection, $attributeValue, $attributeCode, $equals)
    {
        $filter = [];
        foreach (explode(',', $attributeValue) as $val) {
            $filter[] = [
                'attribute' => $attributeCode,
                'finset' => $val
            ];
        }
        if (!empty($filter)) {
            $collection->addAttributeToFilter($filter);
            if (!$equals) {
                $where = $collection->getSelect()->getPart(\Zend_Db_Select::WHERE);
                $fInSetCondition = array_pop($where);
                $fInSetCondition = str_replace('FIND_IN_SET', 'NOT FIND_IN_SET', $fInSetCondition);
                $fInSetCondition = str_replace(
                    \Zend_Db_Select::SQL_OR,
                    \Zend_Db_Select::SQL_AND,
                    $fInSetCondition
                );
                $where[] = $fInSetCondition;
                $collection->getSelect()->setPart(\Zend_Db_Select::WHERE, $where);
            }
        }
    }

    /**
     * @param ProductModel $product
     * @param string $attributeCode
     * @return bool
     */
    private function isAttributeMultiselect(ProductModel $product, $attributeCode)
    {
        return $product->getResource()->getAttribute($attributeCode)->getFrontendInput() == 'multiselect';
    }

    /**
     * @param Collection $collection
     * @param ProductModel $product
     * @param $equals
     *
     * @return bool
     */
    private function addCategoryFilter(Collection $collection, ProductModel $product, $equals)
    {
        $applied = false;
        $categoryIds = $product->getCategoryIds();
        if ($this->getConfigHelper()->isIgnoreAnchorCategories() && $categoryIds) {
            $categoryIds = $this->removeAnchorCategories($product->getCategoryCollection());
        }

        if ($categoryIds) {
            $applied = true;
            //do not use resource model because adding new dependency into this class is not a good option
            $collection->getSelect()->join(
                ['ccproduct' => $this->_productResource->getTable('catalog_category_product')],
                sprintf(
                    'e.entity_id = ccproduct.product_id AND ccproduct.category_id %s(%s)',
                    $equals ? 'IN' : 'NOT IN',
                    implode(',', $categoryIds)
                ),
                []
            );
            $collection->getSelect()->group('e.entity_id');
        }

        return $applied;
    }

    /**
     * @param CategoryCollection $categoryCollection
     * @return array
     */
    private function removeAnchorCategories(CategoryCollection $categoryCollection)
    {
        $categoryCollection->addAttributeToFilter('is_anchor', ['eq' => 0]);
        $categoryCollection->getSelect()->columns('entity_id');
        $categoryIds = $categoryCollection->getAllIds();

        return $categoryIds;
    }

    /**
     * @param ProductModel $product
     * @param string $attributeCode
     * @return mixed
     */
    private function getAttributeValue(ProductModel $product, $attributeCode)
    {
        return $product->getResource()->getAttributeRawValue(
            $product->getId(),
            $attributeCode,
            $product->getStoreId()
        );
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = array_fill_keys([
                'string',
                'numeric',
                'date',
                'select',
                'boolean',
                'multiselect',
                'grid',
                'category',
                'sku'
            ], ['==', '!=']);
            $this->_arrayInputTypes[] = 'category';
        }

        return $this->_defaultOperatorInputByType;
    }

    /**
     * @return string
     */
    public function getValueElementHtml()
    {
        return __('same as Current Product ') . $this->getAttributeObject()->getDefaultFrontendLabel();
    }

    /**
     * @return array
     */
    public function getAttributeSelectOptions()
    {
        $opt = [
            [
                'value' => '1',
                'label' => __('same as Current Product ') . $this->getAttributeObject()->getDefaultFrontendLabel()
            ]
        ];

        return $opt;
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        return $this->getAttributeSelectOptions();
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getFormName()
    {
        return \Amasty\Mostviewed\Model\Group::FORM_NAME;
    }

    private function getConfigHelper(): ConfigHelper
    {
        return $this->getData('config_helper');
    }
}
