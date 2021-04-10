<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\ResourceModel;

use Amasty\ShippingTableRates\Model\Import\Rate\Import;
use Magento\Framework\FlagManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Rate Resource
 */
class Rate extends AbstractDb
{
    const MAIN_TABLE = 'amasty_table_rate';

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }

    public function __construct(
        FlagManager $flagManager,
        TableMaintainer $tableMaintainer,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->flagManager = $flagManager;
        $this->tableMaintainer = $tableMaintainer;
    }

    /**
     * @param int $methodId
     */
    public function deleteBy($methodId)
    {
        $this->getConnection()->delete($this->getMainTable(), 'method_id=' . (int)$methodId);
    }

    /**
     * @param array $data
     */
    public function insertBunch(array $data): void
    {
        $this->getConnection()->insertMultiple($this->tableMaintainer->getTable(self::MAIN_TABLE), $data);
    }

    /**
     * @return string
     */
    public function getMainTable()
    {
        if ($this->flagManager->getFlagData(Import::IMPORT_STATE_KEY) == Import::STATE_ACTIVE) {
            return $this->tableMaintainer->getReplicaTable(self::MAIN_TABLE);
        }

        return parent::getMainTable();
    }
}
