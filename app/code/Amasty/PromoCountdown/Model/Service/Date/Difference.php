<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Model\Service\Date;

class Difference implements \Amasty\PromoCountdown\Api\Service\DateDifferenceInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
    }

    /**
     * Return current timestamp with timezone offset.
     *
     * @return int
     */
    private function getCurrentTime()
    {
        return $this->dateTime->timestamp() + $this->dateTime->getGmtOffset();
    }

    /**
     * @inheritdoc
     */
    public function getDifference($start, $end)
    {
        if (($start - $this->getCurrentTime()) <= 0 && ($result = $end - $this->getCurrentTime()) > 0) {
            return $result;
        } else {
            return 0;
        }
    }
}
