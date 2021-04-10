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

namespace Mirasvit\Feed\Api\Data;


interface RuleInterface
{
    const TABLE_NAME             = 'mst_feed_rule';
    const REL_FEED_TABLE_NAME    = 'mst_feed_rule_feed';
    const REL_PRODUCT_TABLE_NAME = 'mst_feed_rule_product';

    const ID                    = 'rule_id';
    const NAME                  = 'name';
    const IS_ACTIVE             = 'is_active';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';

    public function getId();

    public function setName(string $value): RuleInterface;

    public function getName(): string;

    public function setIsActive(bool $value): RuleInterface;

    public function isActive(): bool;

    public function getConditionsSerialized(): string;

    public function setConditionsSerialized(string $value): RuleInterface;
}
