<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */

namespace Amasty\Stripe\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;

/**
 * Config Provider
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_APPLE_PAY = 'enable_apple_pay';
    const KEY_PUBLIC_KEY = 'public_key';
    const KEY_PRIVATE_KEY = 'private_key';
    const SAVE_CARDS = 'save_customer_card';
    const KEY_SDK_URL = 'sdk_url';
    const IMAGE = 'Amasty_Stripe::img/stripe.png';
    const CONFIG_PATH_LOGO_ENABLED = 'payment/amasty_stripe/logo';
    const KEY_THREE_D_SECURE_ALWAYS = 'three_d_secure_always';
    const KEY_ORDER_STATUS = 'order_status';
    const KEY_EMAIL_RECEIPTS = 'email_receipts';
    const KEY_PAYMENT_ACTION = 'payment_action';

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepository,
        Encryptor $encryptor,
        UrlInterface $urlBuilder,
        $pathPattern = self::DEFAULT_PATH_PATTERN,
        $methodCode = null
    ) {
        $this->encryptor = $encryptor;
        $this->assetRepo = $assetRepository;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     *
     * @return bool
     */
    public function isApplePayEnabled()
    {
        return (bool)$this->getValue(self::KEY_APPLE_PAY);
    }

    /**
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->getValue(self::KEY_PUBLIC_KEY);
    }

    /**
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->getValue(self::KEY_PRIVATE_KEY);
    }

    /**
     *
     * @return string
     */
    public function getSdkUrl()
    {
        return $this->getValue(self::KEY_SDK_URL);
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        $asset = $this->assetRepo->createAsset(self::IMAGE);

        return $this->isLogoActive() ? $asset->getUrL() : '';
    }

    /**
     * @return bool
     */
    public function isEnableSaveCards()
    {
        return (bool)$this->getValue(self::SAVE_CARDS);
    }

    /**
     * @return bool
     */
    private function isLogoActive()
    {
        $isLogoActive = $this->scopeConfig->getValue(
            self::CONFIG_PATH_LOGO_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return (bool) $isLogoActive;
    }

    /**
     * @return mixed
     */
    public function getThreedSecureAlways()
    {
        return $this->getValue(self::KEY_THREE_D_SECURE_ALWAYS);
    }

    /**
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->getValue(self::KEY_ORDER_STATUS);
    }

    /**
     * @return bool
     */
    public function isEmailReceiptsEnabled()
    {
        return (bool)$this->getValue(self::KEY_EMAIL_RECEIPTS);
    }

    /**
     * @return mixed
     */
    public function getAuthorizeMethod()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION);
    }

    /**
     * @return string
     */
    public function getSecretUrl()
    {
        return $this->urlBuilder->getUrl("amstripe/paymentintents/data");
    }
}
