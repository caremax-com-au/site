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


namespace Mirasvit\Feed\Controller\Adminhtml\Dynamic;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Mirasvit\Feed\Model\Dynamic\CategoryFactory;

/**
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 */
abstract class Category extends Action
{
    /**
     * @var CategoryFactory
     */
    protected $dynamicCategoryFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * {@inheritdoc}
     * @param CategoryFactory $variableFactory
     * @param Registry        $registry
     * @param Context         $context
     * @param ForwardFactory  $resultForwardFactory
     */
    public function __construct(
        CategoryFactory $variableFactory,
        Registry        $registry,
        Context         $context,
        ForwardFactory  $resultForwardFactory
    ) {
        $this->dynamicCategoryFactory = $variableFactory;
        $this->registry               = $registry;
        $this->context                = $context;
        $this->resultForwardFactory   = $resultForwardFactory;

        parent::__construct($context);
    }

    /**
     * @param mixed $resultPage
     * @return mixed
     */
    protected function _initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magento_Catalog::catalog');

        $resultPage->getConfig()->getTitle()->prepend(__('Advanced Product Feeds'));
        $resultPage->getConfig()->getTitle()->prepend(__('Category Mapping'));

        return $resultPage;
    }

    /**
     * @return \Mirasvit\Feed\Model\Dynamic\Category
     */
    public function initModel()
    {
        $model = $this->dynamicCategoryFactory->create();

        if ($this->getRequest()->getParam('id')) {
            $model->load($this->getRequest()->getParam('id'));
        }

        $this->registry->register('current_model', $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Feed::feed_dynamic_category');
    }
}
