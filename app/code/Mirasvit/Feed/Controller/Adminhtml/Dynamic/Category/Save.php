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


namespace Mirasvit\Feed\Controller\Adminhtml\Dynamic\Category;

use Mirasvit\Feed\Model\Dynamic\CategoryFactory as CategoryFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Mirasvit\Feed\Controller\Adminhtml\Dynamic\Category;
use Mirasvit\Feed\Helper\Data as Helper;

class Save extends Category
{
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param CategoryFactory  $categoryFactory
     * @param Registry         $registry
     * @param Context          $context
     * @param Helper           $helper
     * @param ForwardFactory   $resultForwardFactory
     */
    public function __construct(
        CategoryFactory        $categoryFactory,
        Registry               $registry,
        Context                $context,
        Helper                 $helper,
        ForwardFactory         $resultForwardFactory
    ) {
        $this->helper = $helper;

        parent::__construct($categoryFactory, $registry, $context, $resultForwardFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->helper->removeJS($this->getRequest()->getParams());

        if ($data) {
            $model = $this->initModel();
            $model->setData($data);

            try {
                $model->save();

                $this->messageManager->addSuccessMessage(__('Item was successfully saved'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('Unable to find item to save'));
            return $resultRedirect->setPath('*/*/');
        }
    }
}
