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

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\Feed\Controller\Adminhtml\Dynamic\Attribute as DynamicAttribute;
use Mirasvit\Feed\Model\Dynamic\AttributeFactory;

class Duplicate extends DynamicAttribute
{
    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $attribute = $this->initModel();

            if ($attribute->getId()) {
                $copy = $this->attributeFactory->create()
                    ->setName($attribute->getName() . ' copy')
                    ->setCode($attribute->getCode() . '_copy')
                    ->setConditionsSerialized($attribute->getConditionsSerialized())
                    ->save();
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('*/*/');
        }

        $this->messageManager->addSuccess(__('Dynamic Attribute "%1" was successfully duplicated.', $attribute->getName()));
        return $resultRedirect->setPath('*/*/');
    }
}
