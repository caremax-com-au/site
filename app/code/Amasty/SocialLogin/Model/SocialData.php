<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model;

class SocialData
{
    const APPLE = 'apple';
    const AMSOCIALLOGIN_SOCIAL_LOGIN_PATH = 'amsociallogin/social/login';
    const GENERAL = 'general';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $dir;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $urlBuilder,
        \Amasty\SocialLogin\Model\ConfigData $configData,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->storeManager = $storeManager;
        $this->configData = $configData;
        $this->urlBuilder = $urlBuilder;
        $this->dir = $dir;
    }

    /**
     * @return array
     */
    public function getEnabledSocials()
    {
        $socials = [];
        foreach ($this->getAllSocialTypes() as $type => $label) {
            if ($this->isSocialShow($type)) {
                $sortOrder = (int)$this->configData->getConfigValue($type . '/sort_order');
                /* when two socials have one sort order*/
                while (true) {
                    if (array_key_exists($sortOrder, $socials)) {
                        $sortOrder++;
                    } else {
                        break;
                    }
                }

                $socials[$sortOrder] = [
                    'type' => $type,
                    'label' => $label,
                    'url' => $this->urlBuilder->getUrl(self::AMSOCIALLOGIN_SOCIAL_LOGIN_PATH, [
                        '_query' => ['type' => $type],
                        '_secure' => true
                    ])
                ];
            }
        }
        ksort($socials);

        return $socials;
    }

    /**
     * @param $type
     * @return bool
     */
    private function isSocialShow($type)
    {
        $isDataExist = $this->configData->getConfigValue($type . '/api_key')
            && $this->configData->getSecretKey($type);

        return $this->configData->getConfigValue($type . '/enabled')
            && ($isDataExist || $type == self::APPLE);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isSocialEnabled(string $type)
    {
        $result = false;
        foreach ($this->getEnabledSocials() as $social) {
            if ($social['type'] === $type) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllSocialTypes()
    {
        return [
            'google' => 'Google',
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram',
            'amazon' => 'Amazon',
            'paypal' => 'Paypal',
            'twitch' => 'TwitchTV',
        ];
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBaseAuthUrl()
    {
        $store = $this->storeManager->getStore();

        return $this->urlBuilder->getUrl('amsociallogin/social/callback', [
            '_nosid'  => true,
            '_scope'  => $store->getId(),
            '_secure' => $store->isUrlSecure()
        ]);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNewBaseAuthUrl()
    {
        $store = $this->storeManager->getStore();

        return $this->urlBuilder->getUrl('amsociallogin/social/login', [
            '_nosid'  => true,
            '_scope'  => $store->getId(),
            '_secure' => $store->isUrlSecure()
        ]);
    }

    /**
     * @param $type
     * @return array
     */
    public function getSocialConfig($type)
    {
        $result = [];
        $apiData = [
            'facebook' => ["trustForwarded" => false, 'scope' => 'email, public_profile'],
            'twitter' => ["includeEmail" => true],
            'linkedin' => ["fields" => ['id', 'first-name', 'last-name', 'email-address']],
            'google' => [
                'scope' => 'email profile',
                'authorize_url_parameters' => [
                    "approval_prompt" => "force",
                ],
            ],
            'paypal' => ['scope' => 'openid profile email'],
        ];

        if ($type && array_key_exists($type, $apiData)) {
            $result = $apiData[$type];
        }

        return $result;
    }

    /**
     * @param $type
     * @return string
     */
    public function getRedirectUrl($type)
    {
        $authUrl = $this->getBaseAuthUrl();
        $allSociaTypes = $this->getAllSocialTypes();
        $type = $allSociaTypes[$type] ?? self::GENERAL;

        switch ($type) {
            case self::GENERAL:
                $newUrl = $this->getNewBaseAuthUrl();
                break;
            case 'Facebook':
                $param = 'hauth_done=' . $type;
                break;
            case 'TwitchTV':
                $param = 'hauth_done=Twitch';
                break;
            case 'Twitter':
            case 'Instagram':
                $param = '';
                break;
            default:
                $param = 'hauth.done=' . $type;
        }

        return $newUrl ?? $authUrl . ($param ? (strpos($authUrl, '?') ? '&' : '?') . $param : '');
    }

    /**
     * @param $userProfile
     * @param string $type
     *
     * @return array
     */
    public function createUserData($userProfile, $type)
    {
        $user = get_object_vars($userProfile);
        $user['displayName'] = $user['displayName'] ?: __('New User');
        $name = explode(' ', $user['displayName']);
        $user['firstname'] = $user['firstName'] ?: array_shift($name);
        $user['lastname'] = $user['lastName'] ?: array_shift($name);
        $user['type'] = $type;

        return $user;
    }
}
