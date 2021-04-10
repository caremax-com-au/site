<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales;

class PackHistoryTable
{
    const TABLE_NAME = 'amasty_mostviewed_pack_sales_history';

    const PACK_COLUMN = 'pack_id';
    const PACK_NAME_COLUMN = 'pack_name';
    const ORDER_COLUMN = 'order_id';
    const QTY_COLUMN = 'qty';
}
