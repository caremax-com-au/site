<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Model;

use Amasty\Acart\Model\Mail\MessageBuilder\MessageBuilder;
use Amasty\Acart\Model\Mail\MessageBuilder\MessageBuilderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class History extends AbstractModel
{
    const STATUS_PROCESSING = 'processing';

    const STATUS_SENT = 'sent';

    const STATUS_CANCEL_EVENT = 'cancel_event';

    const STATUS_BLACKLIST = 'blacklist';

    const STATUS_ADMIN = 'admin';

    const STATUS_NOT_NEWSLETTER_SUBSCRIBER = 'not_newsletter_subscriber';

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $_quoteFactory;

    /**
     * @var RuleQuoteFactory
     */
    private $ruleQuoteFactory;

    /**
     * @var \Magento\Framework\Mail\MessageFactory
     */
    private $messageFactory;

    /**
     * @var \Magento\Framework\Mail\TransportInterfaceFactory
     */
    private $mailTransportFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $salesRuleFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection
     */
    private $newsletterSubscriberCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MessageBuilder
     */
    private $messageBuilder;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    private $urlManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Mail\TransportInterfaceFactory $mailTransportFactory,
        \Magento\Framework\Mail\MessageFactory $messageFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Amasty\Acart\Model\RuleQuoteFactory $ruleQuoteFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\SalesRule\Model\RuleFactory $salesRuleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Acart\Model\Config $config,
        \Amasty\Acart\Model\ConfigProvider $configProvider,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection $newsletterSubscriberCollection,
        MessageBuilderFactory $messageBuilderFactory,
        \Amasty\Acart\Model\UrlManager $urlManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        $this->date = $date;
        $this->messageFactory = $messageFactory;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->_quoteFactory = $quoteFactory;
        $this->ruleQuoteFactory = $ruleQuoteFactory;
        $this->stockRegistry = $stockRegistry;
        $this->salesRuleFactory = $salesRuleFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->newsletterSubscriberCollection = $newsletterSubscriberCollection;
        $this->messageBuilder = $messageBuilderFactory->create();
        $this->configProvider = $configProvider;
        $this->urlManager = $urlManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function _construct()
    {
        $this->_init(\Amasty\Acart\Model\ResourceModel\History::class);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     */
    protected function initDiscountPrices(\Magento\Quote\Model\Quote $quote)
    {
        $this->setSubtotal($quote->getSubtotal());
        $this->setGrandTotal($quote->getGrandTotal());
    }

    /**
     * @param null|int $storeId
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore($storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }

        return $this->storeManager->getStore($storeId);
    }

    /**
     * @param bool $testMode
     */
    public function execute($testMode = false)
    {
        if (!$this->_cancel()) {
            $this->setExecutedAt($this->dateTime->formatDate($this->date->gmtTimestamp()))
                ->save();

            if ($testMode) {
                $this->_sendEmail($testMode);
                $status = self::STATUS_SENT;
            } else {
                $blacklist = \Magento\Framework\App\ObjectManager::getInstance()
                    ->create(\Amasty\Acart\Model\Blacklist::class)->load($this->getCustomerEmail(), 'customer_email');

                if ($blacklist->getId()) {
                    $status = self::STATUS_BLACKLIST;
                } elseif (!$this->validateNewsletterSubscribersOnly($this->getCustomerEmail())) {
                    $status = self::STATUS_NOT_NEWSLETTER_SUBSCRIBER;
                } else {
                    $this->_sendEmail($testMode);
                    $status = self::STATUS_SENT;
                }
            }

            $this->setStatus($status);

            $this->setFinishedAt($this->dateTime->formatDate($this->date->gmtTimestamp()))
                ->save();
        } else {
            $this->setStatus(self::STATUS_CANCEL_EVENT)
                ->save();
            $ruleQuote = $this->ruleQuoteFactory->create()->load($this->getRuleQuoteId());
            $ruleQuote->complete();
        }
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    private function validateNewsletterSubscribersOnly($email)
    {
        if (!$this->config->isEmailsToNewsletterSubscribersOnly($this->getStoreId())) {
            return true;
        }

        /** @var \Magento\Newsletter\Model\Subscriber|null $newsletterSubscriber */
        $newsletterSubscriber = $this->newsletterSubscriberCollection->getItemByColumnValue('subscriber_email', $email);

        if ($newsletterSubscriber
            && $newsletterSubscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     *
     * @return bool|\Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    protected function _getStockItem($quoteItem)
    {
        if (!$quoteItem
            || !$quoteItem->getProductId()
            || !$quoteItem->getQuote()
            || $quoteItem->getQuote()->getIsSuperMode()
        ) {
            return false;
        }

        $stockItem = $this->stockRegistry->getStockItem(
            $quoteItem->getProduct()->getId(),
            $quoteItem->getProduct()->getStore()->getWebsiteId()
        );

        return $stockItem;
    }

    /**
     * @return bool
     */
    protected function _cancel()
    {
        $cancel = false;

        if ($this->getCancelCondition()) {
            foreach (explode(',', $this->getCancelCondition()) as $cancelCondition) {
                $quote = $this->_quoteFactory->create()->load($this->getQuoteId());

                if (!$quote->getId()) {
                    $quote = $quote->loadByIdWithoutStore($this->getQuoteId());
                }

                $quoteValidation = $this->_validateCancelQuote($quote);

                switch ($cancelCondition) {
                    case \Amasty\Acart\Model\Rule::CANCEL_CONDITION_ALL_PRODUCTS_WENT_OUT_OF_STOCK:
                        if (!$quoteValidation['all_products']) {
                            $cancel = true;
                        }
                        break;
                    case \Amasty\Acart\Model\Rule::CANCEL_CONDITION_ANY_PRODUCT_WENT_OUT_OF_STOCK:
                        if (!$quoteValidation['any_products']) {
                            $cancel = true;
                        }
                        break;
                }
            }
        }

        return $cancel;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array
     */
    protected function _validateCancelQuote($quote)
    {
        $inStock = 0;

        foreach ($quote->getAllItems() as $item) {
            $stockItem = $this->_getStockItem($item);

            if ($stockItem) {
                if ($stockItem->getIsInStock()) {
                    $inStock++;
                }
            }
        }

        return [
            'all_products' => (($inStock == 0) ? false : true),
            'any_products' => (((count($quote->getAllItems()) - $inStock) != 0) ? false : true)
        ];
    }

    /**
     * @param bool $testMode
     */
    private function _sendEmail($testMode = false)
    {
        $bcc = $this->configProvider->getBcc($this->getStoreId());
        $isBssMethod = ($this->configProvider->getCopyMethod($this->getStoreId()) == 'bcc');
        $safeMode = $this->config->isSafeMode($this->getStoreId());
        $recipientEmail = $this->configProvider->getRecipientEmailForTest();
        $to = $this->getCustomerEmail();
        $body = $this->getEmailBody();

        if ($testMode || $safeMode) {
            if ($recipientEmail) {
                $to = $recipientEmail;
            } else {
                throw new LocalizedException(
                    __('Please fill in the test email in the extension configuration section')
                );
            }
        }

        if (!$testMode && !$safeMode && $bcc) {
            $bcc = array_map('trim', explode(',', $bcc));

            if (!$isBssMethod) {
                $this->createAndSendMessage($bcc, $this->prepareCopyToEmailBody($body));
                $bcc = null;
            }
        } else {
            $bcc = null;
        }

        $this->createAndSendMessage($to, $body, $bcc);
    }

    /**
     * @param array|string $toEmail
     * @param string $body
     * @param null|array $bcc
     *
     * @throws \Magento\Framework\Exception\MailException
     */
    private function createAndSendMessage($toEmail, $body, $bcc = null)
    {
        $senderName = $this->config->getSenderName($this->getStoreId());
        $senderEmail = $this->config->getSenderEmail($this->getStoreId());
        $replyToEmail = $this->config->getReplyToEmail($this->getStoreId());
        // Compatibility with Mageplaza_Smtp
        $isSetMpSmtpStoreId = $this->_registry->registry('mp_smtp_store_id');

        if ($isSetMpSmtpStoreId === null) {
            $this->_registry->register('mp_smtp_store_id', $this->getStoreId());
        }

        $name = [
            $this->getCustomerFirstname(),
            $this->getCustomerLastname(),
        ];
        // phpcs:ignore
        $emailSubject = html_entity_decode($this->getEmailSubject(), ENT_QUOTES);
        /** @var \Magento\Framework\Mail\Message $message */
        $message = $this->messageFactory->create();
        $message->addTo($toEmail, implode(' ', $name));
        $message->setSubject($emailSubject);

        if (method_exists($message, 'setFromAddress')) {
            $message->setFromAddress($senderEmail, $senderName);
        } else {
            $message->setFrom($senderEmail, $senderName);
        }

        if (method_exists($message, 'setBodyHtml')) {
            $message->setBodyHtml($body);
        } else {
            $message->setBody($body)
                ->setMessageType(\Magento\Framework\Mail\MessageInterface::TYPE_HTML);
        }

        if ($replyToEmail) {
            $message->setReplyTo($replyToEmail);
        }

        if ($bcc) {
            $message->addBcc($bcc);
        }

        if ($message instanceof \Webkul\Rmasystem\Mail\Message) {
            $message->setPartsToBody();
        }

        // This is a compatibility fill for the implemented EmailMessageInterface in Magento 2.3.3.
        if ($this->messageBuilder) {
            $message = $this->messageBuilder->build($message);
        }

        $mailTransport = $this->mailTransportFactory->create(
            [
                'message' => $message
            ]
        );
        $mailTransport->sendMessage();

        if ($isSetMpSmtpStoreId === null) {
            $this->_registry->unregister('mp_smtp_store_id');
        }
    }

    /**
     * @return Rule
     */
    public function getRule()
    {
        return $this->ruleQuoteFactory->create()->loadById($this->getRuleQuoteId())->getRule();
    }

    /**
     * @param string $body
     *
     * @return string
     */
    private function prepareCopyToEmailBody($body)
    {
        $this->urlManager->init($this->getRule(), $this);
        $cartUrl = $this->urlManager->mageUrl('checkout/cart/index');
        $replaceUrl = $this->urlManager->frontUrl();

        return str_replace($cartUrl, $replaceUrl, $body);
    }
}
