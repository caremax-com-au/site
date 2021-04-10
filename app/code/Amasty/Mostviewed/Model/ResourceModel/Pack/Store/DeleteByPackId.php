<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Store;

use Amasty\Mostviewed\Model\Pack\Store\Table;
use Magento\Framework\App\ResourceConnection;

class DeleteByPackId
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $packId): void
    {
        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getTableName(Table::NAME),
            [sprintf('%s = ?', Table::PACK_COLUMN) => $packId]
        );
    }
}
