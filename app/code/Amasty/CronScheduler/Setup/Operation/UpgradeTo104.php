<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CronScheduler
 */


namespace Amasty\CronScheduler\Setup\Operation;

use Amasty\CronScheduler\Api\Data\JobsInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeTo104
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function upgradeSchema(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(CreateJobsTable::TABLE_NAME);
        $connection = $setup->getConnection();

        $connection->changeColumn(
            $table,
            JobsInterface::MODIFIED_SCHEDULE,
            JobsInterface::MODIFIED_SCHEDULE,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function upgradeData(ModuleDataSetupInterface $setup)
    {
        $dataForUpdate = [JobsInterface::MODIFIED_SCHEDULE => null];
        $setup->getConnection()->update(
            $setup->getTable(CreateJobsTable::TABLE_NAME),
            $dataForUpdate,
            [JobsInterface::SCHEDULE . "<>" . JobsInterface::MODIFIED_SCHEDULE]
        );
    }
}
