<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


declare(strict_types=1);

namespace Amasty\Customform\Setup\Operation;

use Amasty\Customform\Api\Data\FormInterface;
use Amasty\Customform\Model\ResourceModel\Form;

class AddDesignColumn
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
            FormInterface::DESIGN,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Form Design',
            ]
        );
    }
}
