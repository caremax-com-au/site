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

use Magento\Backend\Model\View\Result\ForwardFactory;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\Feed\Api\Data\RuleInterface;
use Mirasvit\Feed\Controller\Adminhtml\AbstractRule;
use Mirasvit\Feed\Helper\Data as Helper;
use Mirasvit\Feed\Model\RuleFactory;

class Save extends AbstractRule
{
    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data           = $this->getRequest()->getParams();

        if ($data) {
            $model = $this->initModel();

            $model->setName($data[RuleInterface::NAME])
                ->setIsActive($data[RuleInterface::IS_ACTIVE]);

            if (isset($data['rule'])) {
                $ruleInstance = $this->ruleRepository->getRuleInstance($model);

                $conditions = $ruleInstance->loadPost($data['rule'])
                    ->getConditions()->asArray();

                $model->setConditionsSerialized(SerializeService::encode($conditions));
            }

            try {
                $this->ruleRepository->save($model);

                $feedIds = isset($data['feed_ids']) ? (array)$data['feed_ids'] : [];
                $this->ruleRepository->saveFeedIds($model, $feedIds);

                $this->messageManager->addSuccessMessage(__('Filter was successfully saved'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', [RuleInterface::ID => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [RuleInterface::ID => $this->getRequest()->getParam(RuleInterface::ID)]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('Unable to find item to save'));

            return $resultRedirect->setPath('*/*/');
        }
    }
}
