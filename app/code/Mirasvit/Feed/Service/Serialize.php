<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.30
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Service;

use \Mirasvit\Core\Service\CompatibilityService;

class Serialize
{
    /**
     * @var null | \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer = null;

    public function __construct()
    {
        if (!CompatibilityService::is21() && !CompatibilityService::is20()) {
            $this->serializer = CompatibilityService::getObjectManager()
                ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        }
    }

    /**
     * @param array|mixed $data
     * @return string
     */
    public function serialize($data)
    {
        if (is_resource($data)) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return $this->serializer ? $this->serializer->serialize($data) : \Zend_Json::encode($data);
    }

    /**
     * @param string $string
     * @return array
     * @throws \Zend_Json_Exception
     */
    public function unserialize($string)
    {
        if ('[]' == $string || false === $string || null === $string || '' === $string) {
            return [];
        }
        try {
            $result = $this->serializer ? $this->serializer->unserialize($string) : \Zend_Json::decode($string);
        } catch (\Exception $e) {
            /** mp comment start **/
            $result = unserialize($string);
            /** mp comment end **/
            /** mp uncomment start
            return [];
            mp uncomment end **/
        }
        return is_array($result) ? $result : [];
    }
}
