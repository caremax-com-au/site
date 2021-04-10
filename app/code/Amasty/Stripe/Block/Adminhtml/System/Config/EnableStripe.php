<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class EnableStripe extends Field
{
    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (!class_exists(\Stripe\Stripe::class)) {
            $url = 'https://amasty.com/knowledge-base/topic-stripe-payment-for-magento-2.html'
                . '?utm_source=extension&utm_medium=backend&utm_campaign=stripe-m2-php-lib-is-not-installed#7095';
            $element->setDisabled(true);
            $element->setComment(
                __(
                    "To use Stripe, please install the library stripe/stripe-php since it is required for proper "
                    . "Stripe functioning. Read "
                    . "<a target='_blank' href='%1'>this article</a>" . " for more info.",
                    $url
                )
            );
        }

        return parent::render($element);
    }
}
