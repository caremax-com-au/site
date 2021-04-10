<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Controller\Analytics;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Action\Context;
use Amasty\Mostviewed\Model\Analytics\ViewFactory;
use Amasty\Mostviewed\Api\ViewRepositoryInterface;
use Psr\Log\LoggerInterface;

class View extends Ctr
{
    public function __construct(
        ViewFactory $tempFactory,
        ViewRepositoryInterface $dataRepository,
        SessionManagerInterface $sessionManager,
        LoggerInterface $logger,
        Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        parent::__construct(
            $tempFactory,
            $dataRepository,
            $sessionManager,
            'block_id',
            $logger,
            $context,
            $formKeyValidator
        );
    }
}
