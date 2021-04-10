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



namespace Mirasvit\Feed\Controller;

use Mirasvit\Feed\Api\Data\RuleInterface;

class Registry
{
    /**
     * @var RuleInterface
     */
    private $rule;

    /**
     * @return RuleInterface
     */
    public function getRule()
    {
        return $this->rule;
    }

    public function setRule(RuleInterface $rule)
    {
        $this->rule = $rule;
    }
}
