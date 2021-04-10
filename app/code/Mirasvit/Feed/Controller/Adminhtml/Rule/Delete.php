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



namespace Mirasvit\Feed\Controller\Adminhtml\Rule;

use Mirasvit\Feed\Api\Data\RuleInterface;
use Mirasvit\Feed\Controller\Adminhtml\AbstractRule;

class Delete extends AbstractRule
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $model = $this->initModel();

            $this->ruleRepository->delete($model);

            $this->messageManager->addSuccessMessage(__('Item was successfully deleted'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/edit', [
                RuleInterface::ID => $this->getRequest()->getParam(RuleInterface::ID),
            ]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
