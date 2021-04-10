<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Controller\Adminhtml\Queue;

use Amasty\Base\Helper\Module;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Index extends \Amasty\Acart\Controller\Adminhtml\Queue
{
    const CRON_FAQ_LINK = 'https://amasty.com/knowledge-base/topic-magento-related-questions.html'
        . '?utm_source=extension&utm_medium=link&utm_campaign=abandoned-cart-m2-emails-queue-cron-faq#97';

    const CRON_FAQ_LINK_MARKETPLACE = 'https://amasty.com/docs/doku.php?id=magento_2:abandoned-cart-email'
        . '&utm_source=extension&utm_medium=link&utm_campaign=acart_m2_guide#cron_tasks_list';

    /**
     * @var \Amasty\Acart\Model\Indexer
     */
    private $indexer;

    /**
     * @var \Magento\Framework\Message\Factory
     */
    private $messageFactory;

    /**
     * @var Module
     */
    private $moduleHelper;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        LoggerInterface $logger,
        \Amasty\Acart\Model\Indexer $indexer,
        \Magento\Framework\Message\Factory $messageFactory,
        Module $moduleHelper
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultForwardFactory,
            $logger
        );
        $this->indexer = $indexer;
        $this->messageFactory = $messageFactory;
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $faqLink = self::CRON_FAQ_LINK;
            if ($this->moduleHelper->isOriginMarketplace()) {
                $faqLink = self::CRON_FAQ_LINK_MARKETPLACE;
            }
            $message = __('If there are no emails in the queue for a long time, please make sure that cron is '
            . 'properly configured for your Magento. Please find more information '
                . '<a class="new-page-url" href=\'%1\' target=\'_blank\'>here</a>.', $faqLink);

            $this->messageManager->addMessage(
                $this->messageFactory->create(\Magento\Framework\Message\MessageInterface::TYPE_WARNING, $message)
            );

            $this->indexer->run();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Error. Please see the log for more information.')
            );
            $this->logger->critical($e->__toString());
        }

        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Queue'));

        return $resultPage;
    }
}
