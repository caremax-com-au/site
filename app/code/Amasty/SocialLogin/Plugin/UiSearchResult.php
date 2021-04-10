<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


declare(strict_types=1);

namespace Amasty\SocialLogin\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class UiSearchResult
{
    public function beforeLoad(SearchResult $subject): void
    {
        if (strpos($subject->getMainTable(), 'customer_grid_flat') !== false) {
            $this->injectSelect($subject);
            return;
        }
    }

    /**
     * @param SearchResult $subject
     * @param string|\Zend_Db_Expr $field
     * @param $condition
     */
    public function beforeAddFieldToFilter(SearchResult $subject, $field, $condition = null)
    {
        if (is_string($field) && $this->isNeedAddTablePrefix($field, $subject->getMainTable())) {
            $field = 'main_table.' . $field;
        }

        return [$field, $condition];
    }

    private function isNeedAddTablePrefix(string $field, string $mainTable): bool
    {
        return strpos($mainTable, 'customer_grid_flat') !== false
            && strpos($field, 'sociallogin') === false
            && strpos($field, 'main_table.') === false;
    }

    public function beforeGetSelectCountSql(SearchResult $subject)
    {
        if (strpos($subject->getMainTable(), 'customer_grid_flat') !== false) {
            $this->injectSelect($subject);
        }
    }

    /**
     * @param SearchResult $subject
     */
    private function injectSelect(SearchResult $subject)
    {
        $select = $subject->getSelect();
        if (strpos((string)$select, 'amasty_sociallogin_customers') === false) {
            $select->joinLeft(
                ['sociallogin' => $subject->getTable('amasty_sociallogin_customers')],
                'sociallogin.customer_id=main_table.entity_id',
                [
                    'social_accounts' => 'GROUP_CONCAT(DISTINCT sociallogin.type)'
                ]
            )
                ->group('main_table.entity_id');
        }
    }
}
