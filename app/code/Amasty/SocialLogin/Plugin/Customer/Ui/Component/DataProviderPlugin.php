<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


declare(strict_types=1);

namespace Amasty\SocialLogin\Plugin\Customer\Ui\Component;

use Magento\Customer\Ui\Component\DataProvider;
use Magento\Framework\Api\Filter;

class DataProviderPlugin
{
    public function beforeAddFilter(DataProvider $subject, Filter $filter): array
    {
        if ($filter->getField() == 'social_accounts') {
            $filter->setField('sociallogin.type');
        }

        return [$filter];
    }
}
