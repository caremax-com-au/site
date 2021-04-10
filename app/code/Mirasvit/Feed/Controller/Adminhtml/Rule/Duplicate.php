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

use Magento\Backend\App\Action\Context;
use Mirasvit\Feed\Controller\Adminhtml\AbstractRule;
use Mirasvit\Feed\Controller\Registry;
use Mirasvit\Feed\Repository\RuleRepository;
use Mirasvit\Feed\Service\Rule\DuplicateService;

class Duplicate extends AbstractRule
{
    private $duplicateService;

    public function __construct(
        DuplicateService $duplicateService,
        RuleRepository $ruleRepository,
        Registry $registry,
        Context $context
    ) {
        $this->duplicateService = $duplicateService;

        parent::__construct($ruleRepository, $registry, $context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $model = $this->initModel();

            $this->duplicateService->duplicate($model);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/');
        }

        $this->messageManager->addSuccessMessage(__('Rule was successfully duplicated'));

        return $resultRedirect->setPath('*/*/');
    }
}
