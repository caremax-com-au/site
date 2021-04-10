<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Controller\Social;

use Amasty\SocialLogin\Model\Config\GdprSocialLogin;
use Amasty\SocialLogin\Model\SocialData;
use Hybridauth\Storage\Session;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;

class Login extends \Magento\Customer\Controller\AbstractAccount
{
    const AMSOCIAL_LOGIN_PARAMS = 'amsocial_login_params';

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $messages = [
        'success' => [],
        'error'   => []
    ];

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Amasty\SocialLogin\Model\Social
     */
    private $socialModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Amasty\SocialLogin\Model\Repository\SocialRepository
     */
    private $socialRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\SocialLogin\Controller\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;

    /**
     * @var \Magento\Customer\Model\AuthenticationInterface
     */
    private $authentication;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var \Amasty\SocialLogin\Model\SocialData
     */
    private $socialData;

    /**
     * @var array
     */
    private $redirectTo;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $session,
        \Amasty\SocialLogin\Model\Social $socialModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Amasty\SocialLogin\Model\Repository\SocialRepository $socialRepository,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        \Amasty\SocialLogin\Controller\ResponseHelper $responseHelper,
        \Magento\Customer\Model\AuthenticationInterface $authentication,
        AccountRedirect $accountRedirect,
        CheckoutSession $checkoutSession,
        \Amasty\SocialLogin\Model\SocialData $socialData
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->socialModel = $socialModel;
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->socialRepository = $socialRepository;
        $this->customerRepository = $customerRepository;
        $this->resultRawFactory = $resultRawFactory;
        $this->logger = $logger;
        $this->jsonEncoder = $jsonEncoder;
        $this->responseHelper = $responseHelper;
        $this->accountManagement = $accountManagement;
        $this->customerUrl = $customerUrl;
        $this->authentication = $authentication;
        $this->checkoutSession = $checkoutSession;
        $this->accountRedirect = $accountRedirect;
        $this->socialData = $socialData;
    }

    /**
     * @return Login|bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|string
     */
    public function execute()
    {
        $storage = new Session();
        $type = $this->getRequest()->getParam('type', null);
        if (!$type) {
            $params = array_merge(
                $this->getRequest()->getParams() ?: [],
                $storage->get(Login::AMSOCIAL_LOGIN_PARAMS) ?: []
            );
            $this->getRequest()->setParams($params);
            $type = $this->getRequest()->getParam('type', null);
        }

        if (!$type || !$this->socialData->isSocialEnabled($type)) {
            $this->addErrorMessage(__('Sorry. We cannot find social type. Please try again later.'));

            return $this->returnAction();
        }

        try {
            $storage->set(self::AMSOCIAL_LOGIN_PARAMS, $this->getRequest()->getParams());
            $userProfile = $this->socialModel->getUserProfile($type);
            if (!$userProfile->identifier) {
                $this->addErrorMessage(
                    __('Sorry. We cannot find your email. Please enter email in your %1 profile.', ucfirst($type))
                );

                return $this->returnAction();
            }

            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $customer = $this->socialRepository->getCustomerBySocial($userProfile->identifier, $type, $websiteId);
            if ($this->session->isLoggedIn()) {
                if ($customer && $customer->getId() !== $this->session->getCustomerId()) {
                    $this->addErrorMessage(
                        __('Sorry. We cannot connect to this social profile. It is used for another site account.')
                    );

                    return $this->returnAction();
                } else {
                    $customer = $this->session->getCustomer()->getDataModel();
                    $user = $this->socialData->createUserData($userProfile, $type);
                    $this->socialRepository->createSocialAccount($user, $customer->getId(), $type);
                }
            }

            if (!$customer) {
                $this->session->setUserProfile($userProfile);
                $this->session->setType($type);
                if (!$userProfile->email) {
                    $this->addErrorMessage($this->getErrorMessage($type));
                    $this->createRedirectAfterError($this->_url->getUrl('customer/account/create'));

                    return $this->returnAction();
                }
                $customer = $this->getCustomerProcess($userProfile, $type);
            }

            if ($customer && $this->authenticate($customer)) {
                $this->refresh($customer);
                if ($userProfile && $userProfile->identifier) {
                    $this->session->setAmSocialIdentifier($userProfile->identifier);
                }
                $this->addSuccessMessage(__('You have successfully logged in using your %1 account.', ucfirst($type)));
            }
        } catch (LocalizedException $e) {
            $this->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $message = $e->getMessage();
            if (strpos($message, 'oauth') !== false) {
                $message = __('Please check Callback Url in social app configuration');
            } else {
                $message = __('An unspecified error occurred. Please try again later.');
            }

            $this->addErrorMessage($message);
        }
        $storage->clear();

        return $this->returnAction();
    }

    private function getErrorMessage($type)
    {
        if ($type == SocialData::APPLE) {
            $message = __('Sorry. We cannot find your email. Please sign in, enter My Account ->
                        My Social Accounts tab and link your %1 Social Account.', ucfirst($type));
        } else {
            $message = __('We can`t get customer email from your social account.');
        }

        return $message;
    }

    /**
     * @param $customer
     *
     * @return bool
     */
    private function authenticate($customer)
    {
        $customerId = $customer->getId();
        if ($this->authentication->isLocked($customerId)) {
            $this->addErrorMessage(__('The account is locked.'));
            return false;
        }

        $this->authentication->unlock($customerId);
        $this->_eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return true;
    }

    /**
     * @return Login|bool|string
     */
    private function returnAction()
    {
        $this->updateLastCustomerId();

        $type = count($this->messages['error']) ? 0 : 1;
        $messages = $type ? $this->messages['success'] : $this->messages['error'];
        if ($this->getRequest()->getParam('isAjax')) {
            return $this->setResponseData();
        } else {
            foreach ($messages as $message) {
                if ($type) {
                    $this->messageManager->addSuccessMessage($message);
                } else {
                    $this->messageManager->addErrorMessage($message);
                }
            }
            $this->clearMessages();
            if ($this->redirectTo) {
                $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $result->setUrl($this->redirectTo['url']);
            } else {
                return $this->accountRedirect->getRedirect();
            }
        }
    }

    private function updateLastCustomerId()
    {
        $lastCustomerId = $this->session->getLastCustomerId();
        if (isset($lastCustomerId)
            && $this->session->isLoggedIn()
            && $lastCustomerId != $this->session->getId()
        ) {
            $this->session->unsBeforeAuthUrl()
                ->setLastCustomerId($this->session->getId());
        }
    }

    /**
     * @return $this|string
     */
    private function setResponseData()
    {
        $resultType = count($this->messages['error']) ? 0 : 1;
        $messages = $resultType ? $this->messages['success'] : $this->messages['error'];
        $data = [
            'redirect_data' => $this->redirectTo ?: $this->responseHelper->getRedirectData(),
            'result'   => $resultType,
            'messages' => $messages
        ];

        $this->clearMessages();

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        return $resultRaw->setContents(
            '<script>window.opener.postMessage(' . $this->jsonEncoder->encode($data) . ', "*");window.close();</script>'
        );
    }

    /**
     * @param $userProfile
     * @param $type
     *
     * @return \Amasty\SocialLogin\Model\Customer|bool|CustomerInterface|\Magento\Customer\Model\Customer
     */
    public function getCustomerProcess($userProfile, $type)
    {
        $user = $this->socialData->createUserData($userProfile, $type);

        return $this->getCustomerByUser($user, $type);
    }

    /**
     * @param array $user
     * @param $type
     *
     * @return \Amasty\SocialLogin\Model\Customer|bool|CustomerInterface|\Magento\Customer\Model\Customer
     */
    public function getCustomerByUser($user, $type)
    {
        try {
            $customer = $this->getCustomerByEmail(
                $user['email'],
                $this->storeManager->getStore()->getWebsiteId()
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $customer = null;
        }

        if (!$customer) {
            try {
                $this->validateGdprCheckboxes();
                $model = $this->socialRepository->createCustomer($user);
                $customer = $this->accountManagement->createAccount($model);

                $this->_eventManager->dispatch(
                    'customer_register_success',
                    ['account_controller' => $this, 'customer' => $customer]
                );

                $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
                if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                    $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());

                    $this->addSuccessMessage(
                        __(
                            'You must confirm your account. Please check your email for the confirmation '
                            . 'link or <a href="%1">click here</a> for a new link.',
                            $email
                        )
                    );
                }

                $this->addSuccessMessage(__('We have created new store account using your email address.'));
            } catch (\Magento\Framework\Exception\SecurityViolationException $e) {
                $this->addErrorMessage($e->getMessage());
                return false;
            } catch (\Magento\Framework\Validator\Exception $e) {
                $this->createRedirectAfterError($this->_url->getUrl('customer/account/create'));
                $this->addSuccessMessage(
                    __('The registration process is almost completed! Please kindly fill out a '
                        . 'few more fields to finish it.')
                );
                return false;
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->addErrorMessage(__('An unspecified error occurred during creating new customer.'));
                return false;
            }
        }

        $this->socialRepository->createSocialAccount($user, $customer->getId(), $type);

        return $customer;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Validator\Exception
     */
    protected function validateGdprCheckboxes()
    {
        $result = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch(
            'amasty_gdpr_get_checkbox',
            [
                'scope' => GdprSocialLogin::GDPR_SOCIAL_LOGIN__FORM,
                'result' => $result
            ]
        );

        if ($checkboxes = $result->getData('checkboxes')) {
            foreach ($checkboxes as $checkbox) {
                if ($checkbox->getIsRequired()) {
                    throw new \Magento\Framework\Validator\Exception(__('Please read and accept the privacy policy'));
                }
            }
        }

        return true;
    }

    /**
     * @param string $url
     */
    private function createRedirectAfterError($url)
    {
        $this->redirectTo = [
            'url' => $url,
            'redirect' => '1',
            'redirectWithError' => 1
        ];
    }

    /**
     * @param $customer
     */
    private function refresh($customer)
    {
        if ($customer && $customer->getId()) {
            $this->_eventManager->dispatch('amsociallogin_customer_authenticated');
            $this->session->setCustomerDataAsLoggedIn($customer);
            $this->session->regenerateId();
            $this->checkoutSession->loadCustomerQuote();

            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }
        }
    }

    /**
     * @param $email
     * @param null $websiteId
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerByEmail($email, $websiteId = null)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->customerRepository->get(
            $email,
            $websiteId ?: $this->storeManager->getWebsite()->getId()
        );

        return $customer;
    }

    /**
     * @param $message
     *
     * @return $this
     */
    private function addErrorMessage($message)
    {
        $this->messages['error'][] = $message;

        return $this;
    }

    /**
     * @param $message
     *
     * @return $this
     */
    private function addSuccessMessage($message)
    {
        $this->messages['success'][] = $message;

        return $this;
    }

    /**
     * @return $this
     */
    private function clearMessages()
    {
        $this->messages = [
            'success' => [],
            'error'   => []
        ];

        return $this;
    }
}
