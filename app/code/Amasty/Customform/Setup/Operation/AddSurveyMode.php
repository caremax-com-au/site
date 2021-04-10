<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Setup\Operation;

use Amasty\Customform\Api\Data\FormInterface;
use Magento\Framework\DB\Ddl\Table;
use Amasty\Customform\Model\ResourceModel\Form;

class AddSurveyMode
{
    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(\Magento\Framework\Setup\SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(Form::TABLE);
        $setup->getConnection()->addColumn(
            $tableName,
            FormInterface::SURVEY_MODE_ENABLE,
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Survey mode enable'
            ]
        );
    }
}
