<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Scope config Provider model
 */
class ConfigProvider
{
    /**
     * xpath prefix of module
     */
    const PATH_PREFIX = 'amasty_acart';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    const XPATH_ONLY_CUSTOMERS = 'general/only_customers';

    const XPATH_DEBUG_MODE_EMAIL_DOMAINS = 'debug/debug_emails';

    const XPATH_DEBUG_MODE_ENABLE = 'debug/debug_enable';

    const XPATH_TEST_RECIPIENT = 'testing/recipient_email';

    const XPATH_EMAIL_TEMPLATES_BCC = 'email_templates/bcc';

    const XPATH_EMAIL_TEMPLATES_COPY_METHOD = 'email_templates/copy_method';

    const XPATH_IMG_URL_WITHOUT_PUB = 'email_templates/img_url_without_pub';

    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $key
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getValue($key, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::PATH_PREFIX . '/' . $key, $scopeType, $scopeCode);
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $key
     *
     * @return string|null
     */
    public function getGlobalValue($key)
    {
        return $this->getValue($key, null, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return (bool)$this->getGlobalValue(self::XPATH_DEBUG_MODE_ENABLE);
    }

    /**
     * @return bool
     */
    public function isOnlyCustomers()
    {
        return (bool)$this->getGlobalValue(self::XPATH_ONLY_CUSTOMERS);
    }

    /**
     * @return string
     */
    public function getRecipientEmailForTest()
    {
        $recipientEmail = $this->getGlobalValue(self::XPATH_TEST_RECIPIENT);

        if (empty($recipientEmail) || !\Zend_Validate::is($recipientEmail, 'EmailAddress')) {
            $recipientEmail = false;
        }

        return $recipientEmail;
    }

    /**
     * @return array
     */
    public function getDebugEnabledEmailDomains()
    {
        if ($this->isDebugMode()) {
            return explode(',', $this->getGlobalValue(self::XPATH_DEBUG_MODE_EMAIL_DOMAINS));
        }

        return [];
    }

    /**
     * @return bool
     */
    public function getRemovePubFromImgUrl()
    {
        return (bool)$this->getGlobalValue(self::XPATH_IMG_URL_WITHOUT_PUB);
    }

    /**
     * @param int|string $storeId
     *
     * @return string|null
     */
    public function getBcc($storeId)
    {
        return $this->getValue(self::XPATH_EMAIL_TEMPLATES_BCC, $storeId);
    }

    /**
     * @param int|string $storeId
     *
     * @return string
     */
    public function getCopyMethod($storeId)
    {
        return $this->getValue(self::XPATH_EMAIL_TEMPLATES_COPY_METHOD, $storeId);
    }
}
