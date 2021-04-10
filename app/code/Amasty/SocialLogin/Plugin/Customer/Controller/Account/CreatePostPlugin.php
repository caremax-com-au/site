<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Plugin\Customer\Controller\Account;

use Magento\Customer\Controller\Account\CreatePost;

class CreatePostPlugin
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Amasty\SocialLogin\Model\Repository\SocialRepository
     */
    private $socialRepository;

    /**
     * @var \Amasty\SocialLogin\Model\SocialData
     */
    private $socialData;

    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Amasty\SocialLogin\Model\Repository\SocialRepository $socialRepository,
        \Amasty\SocialLogin\Model\SocialData $socialData
    ) {
        $this->session = $session;
        $this->socialRepository = $socialRepository;
        $this->socialData = $socialData;
    }

    /**
     * @param CreatePost $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(CreatePost $subject, $result)
    {
        $userData = $this->session->getUserProfile();
        if ($userData) {
            $type = $this->session->getType();
            $user = $this->socialData->createUserData($userData, $type);
            $customer = $this->session->getCustomer()->getDataModel();
            $this->socialRepository->createCustomer($user);
            $this->socialRepository->createSocialAccount($user, $customer->getId(), $type);
            $this->session->setUserProfile(null);
        }

        return $result;
    }
}
