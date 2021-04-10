<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Controller\Adminhtml\Instance;

use Amasty\PromoCountdown\Controller\Adminhtml\AbstractInstance;
use Amasty\PromoCountdown\Model\ResourceModel\Widget\Instance\CollectionFactory;
use Magento\Widget\Model\ResourceModel\Widget\Instance;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 * @uses \Amasty\PromoCountdown\Model\ResourceModel\Widget\Instance\Collection
 */
class MassDelete extends AbstractInstance
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var Instance
     */
    private $instanceResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        Instance $instanceResource,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);

        $this->filter = $filter;
        $this->instanceResource = $instanceResource;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $recordDeleted = 0;

        try {
            /** @var Instance $record */
            foreach ($collection->getItems() as $record) {
                $this->instanceResource->delete($record);
                $recordDeleted++;
            }
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Can\'t delete some items. Please review the log and try again.')
            );
        }

        if ($recordDeleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $recordDeleted));
        }

        return $this->_redirect('*/*/');
    }
}
