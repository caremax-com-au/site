<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Model\Adapter;

use Amasty\Stripe\Model\Validator\StripeEnabledValidator;
use Psr\Log\LoggerInterface;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\Stripe;
use Magento\Framework\Module\ModuleListInterface;
use Amasty\Stripe\Gateway\Config\Config;

/**
 * Adapter for Stripe payment
 */
class StripeAdapter
{
    /**
     * User Guide link
     */
    const USER_GUIDE = 'https://amasty.com/docs/doku.php?id=magento_2:stripe-payment';

    /**
     * Namespace and alias of module
     */
    const MODULE_NAME = 'Amasty_Stripe';

    /**
     * Name of application
     */
    const APPLICATION_NAME = 'Magento AmastyStripeM2';

    /**
     * Url to Amasty website
     */
    const APPLICATION_URL = 'https://amasty.com';

    /**
     * Version for API Stripe
     */
    const API_VERSION = '2018-08-23';

    /**
     * Partner Stripe identifier
     */
    const APPLICATION_PARTNER_ID = 'pp_partner_EgRwgkJCSMulZf';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * account id of business owner
     *
     * @var string
     */
    private $accountId;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StripeEnabledValidator
     */
    private $stripeEnabledValidator;

    /**
     * @param Config $config
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        Config $config,
        ModuleListInterface $moduleList,
        LoggerInterface $logger,
        StripeEnabledValidator $stripeEnabledValidator
    ) {
        $this->config = $config;
        $this->moduleList = $moduleList;
        $this->logger = $logger;
        $this->stripeEnabledValidator = $stripeEnabledValidator;

        $this->initCredentials();
    }

    /**
     * Initializes credentials.
     *
     * @return void
     */
    public function initCredentials()
    {
        // Set application version
        // @TODO: it works slow
        $module = $this->moduleList->getOne(self::MODULE_NAME);
        $this->setAppInfo(
            self::APPLICATION_NAME,
            $module['setup_version'],
            self::APPLICATION_URL,
            self::APPLICATION_PARTNER_ID
        );

        if ($this->stripeEnabledValidator->validate()) {
            // Set secret key
            $this->setApiKey($this->config->getPrivateKey());

            // Pinpoint API version
            $this->setApiVersion(self::API_VERSION);

            try {
                $this->setAccountId();
            } catch (\Stripe\Exception\AuthenticationException $exception) {
                $this->logger->critical(
                    (string)__(
                        'No API key provided, or key is incorrect. Please refer to user guide: %1',
                        self::USER_GUIDE
                    )
                );
            } catch (\Stripe\Error\Authentication $exception) {
                $this->logger->critical(
                    (string)__(
                        'No API key provided, or key is incorrect. Please refer to user guide: %1',
                        self::USER_GUIDE
                    )
                );
            }
        }
    }

    /**
     * @param string|null $value
     */
    private function setApiKey($value = null)
    {
        Stripe::setApiKey($value);
    }

    /**
     * @param string $applicationName
     * @param string $applicationVersion
     * @param string $applicationUrl
     * @param string $appPartnerId
     */
    private function setAppInfo($applicationName, $applicationVersion, $applicationUrl, $appPartnerId)
    {
        Stripe::setAppInfo($applicationName, $applicationVersion, $applicationUrl, $appPartnerId);
    }

    /**
     * @param string|null $value
     */
    private function setApiVersion($value = null)
    {
        Stripe::setApiVErsion($value);
    }

    /**
     * Set id for Stripe Account
     */
    private function setAccountId()
    {
        $this->accountId = \Stripe\Account::retrieve()->id;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param $paymentIntentId
     *
     * @return PaymentIntent|null
     */
    public function paymentIntentRetrieve($paymentIntentId)
    {
        try {
            return PaymentIntent::retrieve($paymentIntentId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $sourceId
     *
     * @return null
     */
    public function detachPaymentMethod($sourceId)
    {
        try {
            $paymentMethodStripe = PaymentMethod::retrieve($sourceId);
            $paymentMethodStripe->detach();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $customerId
     *
     * @return \Stripe\Collection|null
     */
    public function listOfCards($customerId)
    {
        try {
            return PaymentMethod::all(['customer' => $customerId, 'type' => 'card', 'limit' => 100]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param array $params
     *
     * @return \Stripe\Customer|null
     */
    public function customerCreate(array $params)
    {
        try {
            return Customer::create($params);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $customerId
     *
     * @return \Stripe\Customer|null
     */
    public function customerRetrieve($customerId)
    {
        return Customer::retrieve($customerId);
    }

    /**
     * @param string $source
     *
     * @return PaymentMethod|null
     */
    public function paymentRetrieve($source)
    {
        try {
            return PaymentMethod::retrieve($source);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $customerId
     *
     * @return bool
     */
    public function customerDelete($customerId)
    {
        try {
            /** @var \Stripe\Customer $customer */
            $customer = Customer::retrieve($customerId);
            $customer->delete();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $chargeId
     * @param null $params
     *
     * @return PaymentIntent|null
     */
    public function chargeCapture($chargeId, $params = null)
    {
        $intent = $this->paymentIntentRetrieve($chargeId);
        if (!($intent instanceof \Stripe\PaymentIntent)) {
            return $intent;
        }

        return $intent->capture($params);
    }

    /**
     * @param array $params
     *
     * @return \Stripe\Refund|\Stripe\Error\Base
     */
    public function refundCreate($params)
    {
        return Refund::create($params);
    }

    /**
     * @param string $paymentIntentId
     * @param array $data
     *
     * @return PaymentIntent
     */
    public function intentPaymentUpdate($paymentIntentId, array $data)
    {
        return PaymentIntent::update($paymentIntentId, $data);
    }

    /**
     * @param PaymentIntent $intent
     * @param array|null $params
     * @param array|string|null $options
     */
    public function paymentIntentCancel(PaymentIntent $intent, $params = null, $options = null)
    {
        $intent->cancel($params, $options);
    }
}
