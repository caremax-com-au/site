<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Adminhtml\Forms;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

class Duplicate extends \Amasty\Customform\Controller\Adminhtml\Forms
{
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('form_id');

        try {
            /** @var \Amasty\Customform\Model\Form $model */
            $model = $this->formRepository->get($id);
            $model->unsetData('form_id');
            $model->setTitle(__('Copy of %1', $model->getTitle()));
            $model->setCode($model->getCode() . '_' . $this->mathRandom->getRandomNumber(0, 50000));
            $model->setData('json_saved', true);
            $model->setStatus(0);
            $this->formRepository->save($model);
            $this->messageManager->addSuccessMessage(__('Custom Form was successfully duplicated'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Custom Form was not found'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('Something went wrong during duplication'));
        }

        $this->_redirect('*/*/index');
    }
}
