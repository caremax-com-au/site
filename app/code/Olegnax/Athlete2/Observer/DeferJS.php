<?php
/**
 * @author      Olegnax
 * @package     Olegnax_DeferJS
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Athlete2\Observer;

use Exception;
use InvalidArgumentException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Olegnax\Athlete2\Helper\Helper;
use Psr\Log\LoggerInterface;

/**
 * Class for modifying js elements in the page source
 */
class DeferJS implements ObserverInterface
{
    const DEFERJS = 'general/deferjs';
    const DEFERJS_COMBINE_INLINE = 'general/deferjs_combine_inline';
    const DEFERJS_COMBINE_MAGENTOINIT = 'general/deferjs_combine_magentoinit';
    const DEFERJS_EXCLUDE_HOMEPAGE = 'general/deferjs_exclude_homepage';
    const DEFERJS_EXCLUDE_ACTION = 'general/deferjs_exclude_action';
    const DEFERJS_EXCLUDE_PATH = 'general/deferjs_exclude_path';
    const DEFERJS_SHOW_ACTION_PATH = 'general/deferjs_show_action_path';
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Json
     */
    protected $serializer;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * FrontSendResponseBefore constructor.
     *
     * @param Helper $helper
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @param Json|null $serializer
     */
    public function __construct(
        Helper $helper,
        RequestInterface $request,
        LoggerInterface $logger,
        Json $serializer = null
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->logger = $logger;
        $this->serializer = $serializer ?: $helper->_loadObject(Json::class);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function mapCondition($value)
    {
        if (is_array($value) && array_key_exists('condition', $value)) {
            $value = $value['condition'];
        }
        return $value;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return;
        }
        $response = $observer->getEvent()->getData('response');
        /** @noinspection PhpUndefinedMethodInspection */
        if (!$response || !$this->helper->getModuleConfig(static::DEFERJS) || $this->request->isXmlHttpRequest()) {
            return;
        }
        if ($this->helper->getModuleConfig(static::DEFERJS_EXCLUDE_HOMEPAGE) && $this->helper->isHomePage()) {
            return;
        }
        $html = $response->getBody();
        if ($html == '') {
            return;
        }

        $pattern = '@<script([^<>]*+(?<!text/x-magento-template.| nodefer))>(.*?)</script>@ims';
        $exclude_action = $this->helper->getModuleConfig(static::DEFERJS_EXCLUDE_ACTION);
        $exclude_path = $this->helper->getModuleConfig(static::DEFERJS_EXCLUDE_PATH);
        try {
            $exclude_action = empty($exclude_action) ? [] : $this->serializer->unserialize($exclude_action);
        } catch (InvalidArgumentException $e) {
            $exclude_action = [];
        }
        $exclude_action = array_map([$this, 'mapCondition'], $exclude_action);

        try {
            $exclude_path = empty($exclude_path) ? [] : $this->serializer->unserialize($exclude_path);
        } catch (InvalidArgumentException $e) {
            $exclude_path = [];
        }
        $exclude_path = array_map([$this, 'mapCondition'], $exclude_path);

        /** @noinspection PhpUndefinedMethodInspection */
        $action = $this->request->getFullActionName();
        /** @noinspection PhpUndefinedMethodInspection */
        $path = $this->request->getPathInfo();
        /** @noinspection PhpUndefinedMethodInspection */
        $org_path = $this->request->getOriginalPathInfo();

        if ((
                !in_array($action, $exclude_action) &&
                !in_array($path, $exclude_path) &&
                !in_array($org_path, $exclude_path)
            ) &&
            preg_match_all($pattern, $html, $_matches)) {
            $html_js = implode('', $_matches[0]);

            // Combine magento init scripts
            if ($this->helper->getModuleConfig(static::DEFERJS_COMBINE_MAGENTOINIT)) {
                $html_js = $this->replaceMagentoInit($html_js);
            }
            // Combine inline scripts
            if ($this->helper->getModuleConfig(static::DEFERJS_COMBINE_INLINE)) {
                $html_js = $this->implodeScript($html_js);
            }
            $html = preg_replace($pattern, '', $html);
            if (preg_match('@</body>@i', $html)) {
                $html = preg_replace('@</body>@i', '</body>', $html);
                $html = str_replace('</body>', $html_js . '</body>', $html);
            } else {
                $html .= $html_js;
            }

            $response->setBody($html);
        }

        // Show Action and Path
        if ($this->helper->getModuleConfig(static::DEFERJS_SHOW_ACTION_PATH)) {
            $new_html = '<table><tr><td>' . __('Action') . '</td><td>' . $action . '</td></tr><tr><td>' . __('Path') . '</td><td>' . $path . '</td></tr><tr><td>' . __('Original Path') . '</td><td>' . $org_path . '</td></tr></table>';
            if (preg_match('@</body>@i', $html)) {
                $html = preg_replace('@</body>@i', $new_html . '</body>', $html);
            } else {
                $html .= $new_html;
            }

            $response->setBody($html);
        }
    }

    /**
     * @param string $html
     * @return string
     */
    protected function replaceMagentoInit($html)
    {
        $pattern = '@<script[^<>]*type="text/x-magento-init"[^<>]*>(.+)</script>@msU';
        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches[1] as $key => $value) {
                try {
                    $_value = $this->serializer->unserialize($value);
                    $matches[1][$key] = is_array($_value) ? $_value : [];
                    $html = str_replace($matches[0][$key], '', $html);
                } catch (Exception $exception) {
                    $this->logger->error(__('Wrong Json structure: ' . $value));
                    unset($matches[1][$key]);
                }
            }
            /** @var array $matches */
            $matches = call_user_func_array('array_replace_recursive', $matches[1]);
            $js = '<script type="text/x-magento-init">' . $this->serializer->serialize($matches) . '</script>';
            $html .= $js;
        }

        return $html;
    }

    /**
     * @param string $html
     * @return string
     */
    protected function implodeScript($html)
    {
        $pattern = '@(<script[^<>]*>)(.*)</script>@msU';
        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {

            $new_html = [];
            $script = [];
            foreach ($matches as $match) {
                if (preg_match('@src="@i', $match[1])) {
                    if (!empty($script)) {
                        $script[1] = preg_replace('@^\s+@', '', $script[1]);
                        $script[1] = preg_replace('@\s+$@', '', $script[1]);
                        $new_html[] = $script[0] . $script[1] . '</script>';
                        $script = [];
                    }
                    $new_html[] = $match[0];
                    continue;
                }
                if (preg_match('@type="(text|application)/javascript"@i', $match[1]) ||
                    !preg_match('@type="([^"]+)"@i', $match[1])
                ) {
                    if (empty($script)) {
                        $script[0] = $match[1];
                        $script[1] = '';
                    }
                    $script[1] .= rtrim(trim($match[2]), ';') . ";\n";
                } else {
                    $new_html[] = $match[0];
                }
            }
            if (!empty($script)) {
                $new_html[] = $script[0] . $script[1] . '</script>';
            }
            $html = implode('', $new_html);
        }

        return $html;
    }
}
