<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


declare(strict_types=1);

namespace Amasty\Stripe\Model\Validator;

use Amasty\Stripe\Gateway\Config\Config;

class StripeEnabledValidator
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        if (!class_exists(\Stripe\Stripe::class)) {
            return false;
        }

        return $this->config->isActive() && $this->config->getPublicKey() && $this->config->getPrivateKey();
    }
}
