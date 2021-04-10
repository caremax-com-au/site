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



namespace Mirasvit\Feed\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mirasvit\Feed\Api\Data\RuleInterface;
use Mirasvit\Feed\Controller\Registry;
use Mirasvit\Feed\Repository\RuleRepository;

abstract class AbstractRule extends Action
{
    protected $ruleRepository;

    protected $context;

    private   $registry;

    public function __construct(
        RuleRepository $ruleRepository,
        Registry $registry,
        Context $context
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->registry       = $registry;
        $this->context        = $context;

        parent::__construct($context);
    }

    /**
     * @return RuleInterface|null
     */
    public function initModel()
    {
        $model = $this->ruleRepository->create();

        if ($this->getRequest()->getParam(RuleInterface::ID)) {
            $model = $this->ruleRepository->get((int)$this->getRequest()->getParam(RuleInterface::ID));
        }

        if ($model) {
            $this->registry->setRule($model);
        }

        return $model;
    }

    /**
     * @param \Magento\Framework\View\Result\Layout $page
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    protected function initPage($page)
    {
        $page->setActiveMenu('Magento_Catalog::catalog');
        $page->getConfig()->getTitle()->prepend(__('Advanced Product Feeds'));
        $page->getConfig()->getTitle()->prepend(__('Filters'));

        return $page;
    }

    protected function _isAllowed()
    {
        return $this->context->getAuthorization()
            ->isAllowed('Mirasvit_Feed::feed');
    }
}
