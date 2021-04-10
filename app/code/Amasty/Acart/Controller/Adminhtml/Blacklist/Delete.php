<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Controller\Adminhtml\Blacklist;

use Amasty\Acart\Model\Blacklist;
use Psr\Log\LoggerInterface;

class Delete extends \Amasty\Acart\Controller\Adminhtml\Blacklist
{
    /**
     * Delete promo quote action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create(Blacklist::class);
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the blacklist.'));
                $this->_redirect('amasty_acart/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete the blacklist right now. Please review the log and try again.')
                );
                $this->_objectManager->get(LoggerInterface::class)->critical($e);
                $this->_redirect('amasty_acart/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a blacklist to delete.'));
        $this->_redirect('amasty_acart/*/');
    }
}
