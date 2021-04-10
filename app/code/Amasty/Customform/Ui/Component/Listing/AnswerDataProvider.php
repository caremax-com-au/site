<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Ui\Component\Listing;

use Amasty\Customform\Model\Config\Source\Status;
use Magento\Framework\Api\Filter;

class AnswerDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var array
     */
    private $mappedFields = [
        'survey' => 'amcform_from.survey_mode_enable',
        'form_id' => 'main_table.form_id',
        'created_at' => 'main_table.created_at'
    ];

    /**
     * @param \Magento\Framework\Api\Search\SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(\Magento\Framework\Api\Search\SearchResultInterface $searchResult)
    {
        $result = [
            'items'        => [],
            'totalRecords' => $searchResult->getTotalCount(),
        ];

        foreach ($searchResult->getItems() as $item) {
            $result['items'][] = $item->getData();
        }

        return $result;
    }

    /**
     * @param Filter $filter
     * @return mixed|void
     */
    public function addFilter(Filter $filter)
    {
        if (array_key_exists($filter->getField(), $this->mappedFields)) {
            $mappedField = $this->mappedFields[$filter->getField()];
            $filter->setField($mappedField);
        }

        parent::addFilter($filter);
    }
}
