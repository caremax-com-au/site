<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.30
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Feed\Model\Rule\Condition;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Mirasvit\Core\Service\CompatibilityService;

class QueryBuilder
{
    const STATIC_FIELDS = ['entity_id', 'sku', 'attribute_set_id', 'type_id', 'created_at', 'updated_at'];

    private $salt = 0;

    private $resource;

    private $connection;

    private $eavConfig;

    public function __construct(
        ResourceConnection $resource,
        EavConfig $eavConfig
    ) {
        $this->resource   = $resource;
        $this->connection = $resource->getConnection();
        $this->eavConfig  = $eavConfig;
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function buildCondition(Select $select, string $fieldName, string $operator, string $value): string
    {
        $fieldCondition = $this->joinField($select, $fieldName);

        if (!$fieldCondition) {
            return '';
        }

        if (in_array($operator, ['()', '!()', '{}', '!{}'])) {
            // remove spaces from the string
            $value = preg_replace('/\s+/', '', $value);

            $value = array_filter(explode(',', $value));
        }

        switch ($operator) {
            case '==':
                return $this->conditionEQ($fieldCondition, $value);

            case '!=':
                return $this->conditionNEQ($fieldCondition, $value);

            case '()':
                return $this->conditionIsOneOf($fieldCondition, $value);

            case '!()':
                return $this->conditionNotIsOneOf($fieldCondition, $value);

            case '<=>':
                return $this->conditionIsUndefined($fieldCondition);

            case '>':
                return $this->conditionGt($fieldCondition, $value);

            case '>=':
                return $this->conditionGtEq($fieldCondition, $value);

            case '<':
                return $this->conditionLt($fieldCondition, $value);

            case '<=':
                return $this->conditionLtEq($fieldCondition, $value);

            case '{}':
                return $this->conditionContains($fieldCondition, $value);

            case '!{}':
                return $this->conditionDoesNotContain($fieldCondition, $value);

            default:
                return '';
        }
    }

    private function joinField(Select $select, string $fieldName): string
    {
        if (in_array($fieldName, self::STATIC_FIELDS)) {
            $fieldCondition = "e.{$fieldName}";

            $select->columns([
                $fieldName => $fieldCondition,
            ]);

            return $fieldCondition;
        }

        $attribute = $this->eavConfig->getAttribute('catalog_product', $fieldName);

        if (!$attribute->getId()) {
            return '';
        }

        $salt = '_' . $this->salt++;
        $code = $attribute->getAttributeCode();

        if ($code == 'category_ids') {
            $table      = $this->resource->getTableName('catalog_category_product');
            $tableAlias = "tbl_{$code}{$salt}";
            $field      = "{$tableAlias}.category_id";
            $select->joinLeft(
                [$tableAlias => $table],
                "e.entity_id = {$tableAlias}.product_id",
                [$code = $field]
            );

            return $field;
        }

        $table = $attribute->getBackendTable();

        $tableAlias = "tbl_{$code}{$salt}";

        $field = "{$tableAlias}.value";

        if (!$this->isJoined($select, $field)) {
            if (CompatibilityService::isEnterprise()) {
                $condition = "e.row_id = {$tableAlias}.row_id AND {$tableAlias}.attribute_id = {$attribute->getId()}";
            } else {
                $condition = "e.entity_id = {$tableAlias}.entity_id AND {$tableAlias}.attribute_id = {$attribute->getId()}";
            }

            $select->joinLeft(
                [$tableAlias => $table],
                $condition,
                [$code => $field]
            );
        }

        return $field;
    }

    private function conditionEQ(string $field, string $value): string
    {
        if ($value == ''){
            return $this->connection->quoteInto("${field} = ? or ${field} IS NULL", $value);
        } else {
            return $this->connection->quoteInto("${field} = ?", $value);
        }
    }

    private function conditionNEQ(string $field, string $value): string
    {
        return $this->connection->quoteInto("${field} NOT IN (?)", $value);
    }

    private function conditionGt(string $field, string $value): string
    {
        return $this->connection->quoteInto("${field} > ?", $value);
    }

    private function conditionGtEq(string $field, string $value): string
    {
        return $this->connection->quoteInto("${field} >= ?", $value);
    }

    private function conditionLt(string $field, string $value): string
    {
        return $this->connection->quoteInto("${field} < ?", $value);
    }

    private function conditionLtEq(string $field, string $value): string
    {
        return $this->connection->quoteInto("${field} <= ?", $value);
    }

    private function conditionIsOneOf(string $field, array $value): string
    {
        $parts = [];
        foreach ($value as $v) {
            $parts[] = $this->connection->quoteInto("FIND_IN_SET(?, {$field})", $v);
        }

        return implode(' OR ', $parts);
    }

    private function conditionContains(string $field, array $value): string
    {
        $parts = [];
        foreach ($value as $v) {
            $parts[] = $this->connection->quoteInto("{$field} LIKE ?", '%' . $v . '%');
        }

        return implode(' OR ', $parts);
    }

    private function conditionDoesNotContain(string $field, array $value)
    {
        $parts = [];
        foreach ($value as $v) {
            $parts[] = $this->connection->quoteInto("{$field} NOT LIKE ?", '%' . $v . '%');
        }

        return implode(' AND ', $parts);
    }

    private function conditionNotIsOneOf(string $field, array $value): string
    {
        $parts = [];
        foreach ($value as $v) {
            $parts[] = $this->connection->quoteInto("FIND_IN_SET(?, {$field}) = 0", $v);
        }

        return implode(' AND ', $parts);
    }

    private function conditionIsUndefined(string $field): string
    {
        $parts = [
            "{$field} IS NULL",
            "{$field} = ''",
        ];

        return implode(' OR ', $parts);
    }

    private function isJoined(Select $select, string $field): bool
    {
        return strpos((string)$select, $field) !== false;
    }
}
