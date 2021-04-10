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


namespace Mirasvit\Feed\Controller\Adminhtml\Dynamic\Attribute;

use Mirasvit\Feed\Controller\Adminhtml\Dynamic\Attribute as DynamicAttribute;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Mirasvit\Feed\Model\Dynamic\AttributeFactory;
use Mirasvit\Feed\Helper\Data as Helper;

class Save extends DynamicAttribute
{
    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param AttributeFactory $attributeFactory
     * @param Helper           $helper
     * @param Registry         $registry
     * @param Context          $context
     * @param ForwardFactory   $resultForwardFactory
     */
    public function __construct(
        AttributeFactory $attributeFactory,
        Helper           $helper,
        Registry         $registry,
        Context          $context,
        ForwardFactory   $resultForwardFactory
    ) {
        $this->helper = $helper;

        parent::__construct($attributeFactory, $registry, $context, $resultForwardFactory);
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
            $data = $this->filterValues($data);
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

    /**
     * @param array $data
     * @return array
     */
    public function filterValues($data)
    {
        if (isset($data['conditions'])) {
            $data['conditions'] = array_values($data['conditions']);
        }

        return $data;
    }
}
