<?php

/**
 * Athlete2 Theme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Helper;

use Magento\Catalog\Helper\Product\Compare;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Helper\Data;
use Olegnax\Athlete2\Block\ChildTemplate;
use Olegnax\Athlete2\Model\Api;
use Olegnax\Athlete2\Model\Client;
use Olegnax\Athlete2\Model\Encryption;
use Olegnax\Athlete2\Model\LicenseRequest;

class Helper extends AbstractHelper
{

    const XML_ENABLED = 'athlete2_settings/general/enable';
    const CONFIG_MODULE = 'athlete2_settings';
    const PRODUCT_CODE = '23693737';
    const STORE_CODE = '89MBca8Id0G8P61';
    const CHILD_TEMPLATE = ChildTemplate::class;

    /**
     *
     * @var ObjectManager
     */
    public $objectManager;
    protected $_lazyExcludeClass;

    public function __construct(Context $context)
    {
        $this->objectManager = ObjectManager::getInstance();

        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getConfig(static::XML_ENABLED);
    }

    public function getLayoutTemplateHtml($block, $option_path = '', $fileName = '', $arguments = [])
    {
        $value = $this->getConfig($option_path);

        if (is_string($value) || is_numeric($value)) {
            return $this->getLayoutTemplateHtmlbyValue($block, $value, $fileName, $arguments);
        }
    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->getSystemValue($path, $storeCode);
    }

    public function getSystemValue($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    public function getLayoutTemplateHtmlbyValue(
        $block,
        $value = null,
        $fileName = null,
        $arguments = [],
        $separator = '/'
    ) {
        $_fileName = '';
        if (empty($fileName)) {
            $blockTemplate = $block->getTemplate();
            if (preg_match('/(\.[^\.]+?)$/', $blockTemplate)) {
                $fileName = preg_replace('/(\.[^\.]+?)$/', '%s%s$1', $blockTemplate);
            } else {
                $fileName .= '%s%s';
            }
            if (!preg_match('#([^_\:])_([^_\:])\:\:#i', $fileName)) {
                $className = array_slice(array_filter(explode('\\', get_class($block))), 0, 2);
                if ('Magento' !== $className[0]) {
                    $fileName = implode('_', $className) . '::' . $fileName;
                }
            }
        } else {
            $_fileName = $fileName;
        }
        $blockName = $separator . $block->getNameInLayout() . $separator . $_fileName . $separator . $value;
        $fileName = sprintf($fileName, $separator, $value);
        while ($block->getLayout()->getBlock($blockName)) {
            $blockName .= '_0';
        }
        $_block = $block->getLayout()->createBlock(static::CHILD_TEMPLATE, $blockName);
        $block->setChild($_block->getNameInLayout(), $_block);
        if (!empty($arguments) && is_array($arguments)) {
            $_block->addData($arguments);
        }
        $content = $_block->setTemplate($fileName)->toHtml();

        return $content;
    }

    public function isAdmin()
    {
        return $this->isArea(Area::AREA_ADMINHTML);
    }

    public function isArea($area = Area::AREA_FRONTEND)
    {
        if (!isset($this->isArea[$area])) {
            /** @var State $state */
            $state = $this->_loadObject(State::class);

            try {
                $this->isArea[$area] = ($state->getAreaCode() == $area);
            } catch (\Exception $e) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function _loadObject($path)
    {
        return $this->objectManager->get($path);
    }

    public function setModuleConfig(
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        if (!empty($path)) {
            $path = static::CONFIG_MODULE . '/' . $path;
        }
        return $this->setSystemValue($path, $value, $scope, $scopeId);
    }

    public function setSystemValue($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        return $this->_loadObject(WriterInterface::class)->save($path, $value, $scope, $scopeId);
    }

    public function deleteSystemValue($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        return $this->_loadObject(WriterInterface::class)->delete($path, $scope, $scopeId);
    }

    public function getModuleConfig($path = '', $storeCode = null)
    {
        if (empty($path)) {
            $path = static::CONFIG_MODULE;
        } else {
            $path = static::CONFIG_MODULE . '/' . $path;
        }
        return $this->getSystemValue($path, $storeCode);
    }

    /**
     * @param string $path
     * @param array $arguments
     * @return mixed
     */
    public function _createObject($path, $arguments = [])
    {
        return $this->objectManager->create($path, $arguments);
    }

    public function isLoggedIn()
    {
        return $this->getSession()->isLoggedIn();
    }

    public function getSession()
    {
        return ObjectManager::getInstance()->create(Session::class);
    }

    function getWishlistCount()
    {
        $this->getSession();
        return $this->_loadObject(Data::class)->getItemCount();
    }

    function getCompareListUrl()
    {
        $this->getSession();
        return $this->_loadObject(Compare::class)->getListUrl();
    }

    function getCompareListCount()
    {
        $this->getSession();
        return $this->_loadObject(Compare::class)->getItemCount();
    }

    public function isActivePlugin($name)
    {
        return $this->_moduleManager->isOutputEnabled($name);
    }

    public function getSystemDefaultValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
    }

    function getVersion()
    {
        return $this->_loadObject(ProductMetadataInterface::class)->getVersion();
    }

    private function messageManager()
    {
        return $this->_loadObject(ManagerInterface::class);
    }

    public function configFactory()
    {
        return $this->_loadObject(ConfigInterface::class);
    }

    public function clearCache($types = ['config'])
    {
        $cacheTypeList = $this->_loadObject(TypeListInterface::class);
        $CacheFrontendPool = $this->_loadObject(Pool::class);
        foreach ($types as $type) {
            $cacheTypeList->cleanType($type);
        }
        foreach ($CacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

    public function getBlockTemplateProcessor($content = '')
    {
        return $this->_loadObject(FilterProvider::class)->getBlockFilter()->filter(trim($content));
    }

    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }

    public function getUrl($route = '', $params = [])
    {
        /** @var UrlInterface $urlBuilder */
        $urlBuilder = $this->_loadObject(UrlInterface::class);
        return $urlBuilder->getUrl($route, $params);
    }

    public function isAccountPage()
    {
        $request = $this->objectManager->get(\Magento\Framework\App\Action\Context::class)->getRequest();
        return $request->getFullActionName() == 'customer_account_login';
    }

    public function isMobile()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $result = false;
        if (!empty($user_agent)) {
            $result = preg_match(
				'/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino|android|ipad|playbook|silk/i',$user_agent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
				substr($user_agent,0,4)
                );
        }

        return $result;
    }

    /**
     * @param $license_key
     * @return object|\Olegnax\Athlete2\Model\stdClass
     * @throws \Exception
     */
    public function activate($license_key)
    {
        if (empty($license_key)) {
            throw new \Exception(__('License Key can not be empty.'));
        }
        $store_code = static::STORE_CODE;
        $sku = static::PRODUCT_CODE;
        $frequency = LicenseRequest::DAILY_FREQUENCY;
        $domain = parse_url($this->getSystemDefaultValue('web/unsecure/base_url'), PHP_URL_HOST);
        $domain = preg_replace('#^www\.#i','', $domain);

        $response = Api::activate(
            Client::instance(),
            function () use (&$store_code, &$sku, &$license_key, &$frequency, &$domain) {
                return LicenseRequest::create(
                    'https://olegnax.com/wp-admin/admin-ajax.php',
                    $store_code,
                    $sku,
                    $license_key,
                    $domain,
                    $frequency
                );
            },
            [&$this, 'encrypt_save']
        );
        return $response;
    }

    /**
     * @param bool $force
     * @return bool
     * @throws \Exception
     */
    public function validate($force = false)
    {
        $license = $this->load_decrypt();
        if ($license === false) {
            return false;
        }
        // Prepare connection retries
        $allow_retry = null;
        $retry_attempts = 0;
        $retry_frequency = null;
        $domain = parse_url($this->getSystemDefaultValue('web/unsecure/base_url'), PHP_URL_HOST);
        $domain = preg_replace('#^www\.#i','', $domain);

        // Validate
        return Api::validate(
            Client::instance(),
            function () use (&$license) {
                return new LicenseRequest($license);
            },
            [&$this, 'encrypt_save'],
            $domain,
            $force, // Force
            $allow_retry === null ? true : $allow_retry,
            $retry_attempts ? $retry_attempts : 2,
            $retry_frequency ? $retry_frequency : '+1 hour'
        );
    }

    /**
     * @return bool|object|\Olegnax\Athlete2\Model\stdClass
     * @throws \Exception
     */
    public function deactivate()
    {
        $license = $this->load_decrypt();
        if ($license === false) {
            return false;
        }
        $domain = parse_url($this->getSystemDefaultValue('web/unsecure/base_url'), PHP_URL_HOST);
        $domain = preg_replace('#^www\.#i','', $domain);
        // Validate
        $response = Api::deactivate(
            Client::instance(),
            function () use (&$license) {
                return new LicenseRequest($license);
            },
            [&$this, 'encrypt_save'],
            $domain
        );
        if ($response && $response->error === true) {
            $this->encrypt_save(null);
            // Force deactivation
            $response->error = false;
            $response->message = __('Deactivated.');
        }

        return $response;
    }

    /**
     * @return false|string
     */
    protected function load_decrypt()
    {
        // Load
        $license = $this->getSystemDefaultValue('athlete2_license/general/license');
        // Decrypt
        if (! empty($license)) {
            $store_code = static::STORE_CODE;
            return Encryption::decode(
                $license,
                $store_code
            );
        } else{
            $license = false;
        }

        return $license;
    }

    /**
     * @return false|mixed|string
     */
    public function get()
    {
        $license_key = $this->load_decrypt();
        
        return $license_key !== false && $this->validate() ? json_decode($license_key) : $license_key;
    }

    /**
     * @param $license
     */
    public function encrypt_save($license)
    {
        // Check for downloadbles and updates
        $new = json_decode($license);
        $license_key = $this->load_decrypt();
        $old = $license_key !== false ? json_decode($license_key) : $license_key;
        $store_code = static::STORE_CODE;
        // Save license string
        $this->configFactory()->saveConfig(
            'athlete2_license/general/license',
            is_string($license) ? Encryption::encode( $license, $store_code ) : '',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $this->configFactory()->saveConfig(
            'athlete2_license/general/avaible_update',
            ($old !== false
                && isset($new->data->downloadable)
                && isset($old->data->downloadable)
                && $new->data->downloadable->name !== $old->data->downloadable->name
            ) ? 1 : 0,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $this->clearCache();
    }

    public function check()
    {
        $license = $this->load_decrypt();
        if ($license === false) {
            return false;
        }

        // Validate and return response
        return Api::check(
            Client::instance(),
            function () use (&$license) {
                return new LicenseRequest($license);
            },
            [&$this, 'encrypt_save']
        );
    }
}

