<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Controller\Adminhtml\Instance;

class Index extends \Amasty\PromoCountdown\Controller\Adminhtml\AbstractInstance
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_PromoCountdown::instances');
        $resultPage->getConfig()->getTitle()->prepend(__('Countdown Widgets List'));

        return $resultPage;
    }
}
