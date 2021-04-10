<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Controller\Form;

use Amasty\Customform\Helper\Data;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;

class Submit extends \Magento\Framework\App\Action\Action
{
    const SUCCESS_RESULT = 'success';

    const ERROR_RESULT = 'error';

    /**
     * @var \Amasty\Customform\Helper\Data
     */
    private $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Amasty\Customform\Model\Submit
     */
    private $submit;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\App\Action\Context $context,
        Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Customform\Model\Submit $submit,
        SessionFactory $sessionFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->logger = $logger;
        $this->session = $session;
        $this->submit = $submit;
        $this->sessionFactory = $sessionFactory;
    }

    public function execute()
    {
        $url = Data::REDIRECT_PREVIOUS_PAGE;
        if ($this->getRequest()->isPost()) {
            try {
                $url = $this->submit->process($this->getRequest()->getParams());
                $this->_eventManager->dispatch(
                    'custom_checkbox_confirm_log',
                    ['customer' => $this->sessionFactory->create()->getCustomer()]
                );
                $type = self::SUCCESS_RESULT;
                $this->session->setData(SessionData::AM_CUSTOM_FORM_SESSION_DATA, []);
            } catch (ValidatorException $e) {
                $this->processError($e, $this->getValidatorExceptionMessage());
            } catch (LocalizedException $e) {
                $this->processError($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->processError($e, $this->getExceptionMessage());
            }
        }

        if ($this->getRequest()->isAjax()) {
            $response = $this->getResponse()->representJson(
                $this->helper->encode(['result' => $type ?? self::ERROR_RESULT])
            );
        } else {
            if ($url === Data::REDIRECT_PREVIOUS_PAGE) {
                $url = $this->_redirect->getRefererUrl();
            }
            $response = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($url);
        }

        return $response;
    }

    /**
     * @param \Exception $e
     * @param \Magento\Framework\Phrase|string $message
     */
    private function processError(\Exception $e, $message)
    {
        $this->logger->error($e->getMessage());
        $this->session->setData(SessionData::AM_CUSTOM_FORM_SESSION_DATA, $this->getRequest()->getParams());
        $this->messageManager->addErrorMessage($message);
    }

    private function getValidatorExceptionMessage(): \Magento\Framework\Phrase
    {
        return __(
            'Server error occurred while saving form data.  Please try again later or use Contact Us link in the menu.'
        );
    }

    private function getExceptionMessage(): \Magento\Framework\Phrase
    {
        return __(
            'Sorry. There is a problem with Your Form Request. Please try again or use Contact Us link in the menu.'
        );
    }
}
