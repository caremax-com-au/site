<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\ResourceModel\Pack;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\ResourceModel\Pack;
use Magento\Framework\App\ResourceConnection;

class LoadPacksOptions
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName(Pack::PACK_TABLE),
            [PackInterface::PACK_ID, PackInterface::NAME]
        );

        return $this->resourceConnection->getConnection()->fetchPairs($select);
    }
}
