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

namespace Mirasvit\Feed\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Feed\Api\Data\ExportableEntityInterface;
use Mirasvit\Feed\Api\Data\RuleInterface;

class Rule extends AbstractModel implements RuleInterface, ExportableEntityInterface
{
    public function getId()
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getName(): string
    {
        return (string)$this->getData(self::NAME);
    }

    public function setName(string $value): RuleInterface
    {
        return $this->setData(self::NAME, $value);
    }

    public function isActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(bool $value): RuleInterface
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    public function getConditionsSerialized(): string
    {
        return (string)$this->getData(self::CONDITIONS_SERIALIZED);
    }

    public function setConditionsSerialized(string $value): RuleInterface
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $value);
    }

    public function getRowsToExport(): array
    {
        return [
            self::NAME,
            self::CONDITIONS_SERIALIZED,
        ];
    }

    protected function _construct()
    {
        parent::_construct();

        $this->_init(ResourceModel\Rule::class);
        $this->setIdFieldName(RuleInterface::ID);
    }
}
