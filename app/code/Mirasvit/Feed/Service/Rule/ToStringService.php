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

namespace Mirasvit\Feed\Service\Rule;

use Mirasvit\Feed\Api\Data\RuleInterface;
use Mirasvit\Feed\Repository\RuleRepository;

class ToStringService
{
    private $ruleRepository;

    public function __construct(RuleRepository $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    public function toString(RuleInterface $rule): string
    {
        $html = $this->ruleRepository->getRuleInstance($rule)
            ->getConditions()->asStringRecursive();
        $html = nl2br(preg_replace('/ /', '&nbsp;', $html));

        return $html;
    }
}
