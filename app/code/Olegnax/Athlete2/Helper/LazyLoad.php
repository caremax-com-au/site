<?php

namespace Olegnax\Athlete2\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class LazyLoad extends AbstractHelper
{

    const PLACEHOLDERE = 'athlete2/placeholder.png';
    const PLACEHOLDER_TEMPLATE = '{dirname}/lazy-placeholders/{width}_{height}.{extension}';
    protected $_lazyExcludeClass;
    protected $isEnabled;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var string
     */
    protected $urlMedia;
    /**
     * @var Image
     */
    protected $imageHelper;
    /**
     * @var false|string
     */
    protected $placeholder;
    /**
     * @var string
     */
    protected $placeholderDefault;

    public function __construct(
        Context $context,
        Image $imageHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->imageHelper = $imageHelper;
        $this->urlMedia = rtrim(preg_replace(
            '@^http[s]*\:@i',
            '',
            $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
        ), "/");

        parent::__construct($context);
    }

    public function _filterImg($html = '')
    {
        if (preg_match('@(data-original=\"|lazy|data-ox-image)@i', $html)) {
            return false;
        }
        $class = $this->getExcludeClass();
        if (!empty($class) && preg_match('@class="([^"]+)"@i', $html, $matches)) {
            $matches = array_filter(explode(' ', $matches[1]));
            $intersect = array_intersect($class, $matches);
            return empty($intersect);
        }
        return true;
    }

    public function getExcludeClass()
    {
        if (empty($this->_lazyExcludeClass)) {
            $class = $this->getConfig('athlete2_settings/general/lazyload_exclude');
            if (empty($class)) {
                $class = ['rev-slidebg'];
            } elseif (preg_match_all('@\S+@', $class, $matches)) {
                $class = array_filter($matches[0]);
            }
            if (is_array($class) && !empty($class)) {
                $this->_lazyExcludeClass = $class;
            }
        }

        return $this->_lazyExcludeClass;
    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }

    public function replaceImageToLazy($html = '')
    {
        if ($this->isEnabled() && preg_match_all('@<img[^<>]+src\=[^<>]+>@ims', $html, $matches)) {

            $matches = array_filter($matches[0], [$this, '_filterImg']);
            if (!empty($matches) && is_array($matches)) {
                $_matches = [];
                $resizedPlaceholder = '1' == $this->getConfig('athlete2_settings/general/lazyload_resized_placeholder');
                foreach ($matches as $htmlImg) {
                    $placeholder = $this->getDefaultPlaceHolder();
                    $_htmlImg = $htmlImg;
                    if ($resizedPlaceholder && preg_match('@src="([^\"]+)"@im', $htmlImg, $matches)) {
                        $size = $this->getSizeImageFromUrl($matches[1]);
                        if (!empty($size)) {
                            try {
                                $image = $this->getPlaceholderHelper()->adaptiveResize($size)->getUrl();
                                $placeholder = $image;
                            } catch (Exception $e) {
                                $this->_logger->error($e->getMessage());
                            }
                            if (!preg_match('@ height="@i', $htmlImg)) {
                                $_htmlImg = preg_replace('@(src="[^\"]+")@im', '$1 height="' . $size[1] . '"',
                                    $_htmlImg);
                            }
                            if (!preg_match('@ width="@i', $htmlImg)) {
                                $_htmlImg = preg_replace('@(src="[^\"]+")@im', '$1 width="' . $size[0] . '"',
                                    $_htmlImg);
                            }
                        }
                    }

                    $_htmlImg = preg_replace('@src="([^\"]+)"@im', 'src="' . $placeholder . '" data-original="$1"',
                        $_htmlImg);
                    if (preg_match('@class="@i', $_htmlImg)) {
                        $_htmlImg = preg_replace('@class="([^\"]+)"@im', 'class="$1 lazy"', $_htmlImg);
                    } else {
                        $_htmlImg = preg_replace('@<img@im', '$0 class="lazy"', $_htmlImg);
                    }
                    $_matches[$htmlImg] = $_htmlImg;
                }
                if (!empty($_matches)) {
                    $search = array_keys($_matches);
                    $replace = array_values($_matches);
                    $html = str_replace($search, $replace, $html);
                }
            }
        }

        return $html;
    }

    public function isEnabled()
    {
        if (is_null($this->isEnabled)) {
            $this->isEnabled = $this->getConfig(Helper::XML_ENABLED) && $this->getConfig('athlete2_settings/general/lazyload');
        }

        return $this->isEnabled;
    }

    public function getDefaultPlaceHolder()
    {
        if (empty($this->placeholderDefault)) {
            $this->placeholderDefault = $this->getViewFileUrl('Olegnax_Core/images/preloader-img.svg');
        }
        return $this->placeholderDefault;
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->_loadObject(RequestInterface::class)->isSecure()], $params);
            return $this->_loadObject(Repository::class)->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            return $this->_getNotFoundUrl();
        }
    }

    protected function _loadObject($object)
    {
        return $this->_getObjectManager()->get($object);
    }

    protected function _getObjectManager()
    {
        return ObjectManager::getInstance();
    }

    /**
     * Get 404 file not found url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _getNotFoundUrl($route = '', $params = ['_direct' => 'core/index/notFound'])
    {
        return $this->getUrl($route, $params);
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_loadObject(UrlInterface::class)->getUrl($route, $params);
    }

    protected function getSizeImageFromUrl($path)
    {
        return $this->getSizeImage($this->getAbsoluteImagePath($path));
    }

    /**
     * @param $path
     * @return array|null
     */
    protected function getSizeImage($path)
    {
        try {
            $image = $this->imageHelper->init($path);
            if (!$image->getOriginalWidth() || !$image->getOriginalHeight()) {
                throw new Exception(__('Image not found: ' . $path));
            }

            return [$image->getOriginalWidth(), $image->getOriginalHeight()];
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());
            return null;
        }
    }

    protected function getAbsoluteImagePath($url)
    {
        $url = preg_replace(
            '@^http[s]*\:@i',
            '',
            $url
        );
        if (false === strpos($url, $this->urlMedia)) {
            $path = preg_replace('@^\/media@i', '', $url);
        } else {
            $path = str_replace($this->urlMedia, '', $url);
        }

        return $path;
    }

    /**
     * @return Image
     * @throws Exception
     */
    protected function getPlaceholderHelper()
    {
        return $this->imageHelper->init(
            static::PLACEHOLDERE,
            [
                'fileTemplate' => static::PLACEHOLDER_TEMPLATE,
                'quality' => 1,
            ]
        );
    }

    public function getPlaceHolder($url)
    {
        $size = $this->getSizeImageFromUrl($url);
        if (!empty($size)) {
            try {
                $image = $this->getPlaceholderHelper()->adaptiveResize($size)->getUrl();
                return $image;
            } catch (Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        }
        return $this->getDefaultPlaceHolder();
    }
}