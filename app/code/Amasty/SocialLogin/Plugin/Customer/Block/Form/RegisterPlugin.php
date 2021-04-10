<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


declare(strict_types=1);

namespace Amasty\SocialLogin\Plugin\Customer\Block\Form;

use Magento\Customer\Block\Form\Register;

class RegisterPlugin
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    public function __construct(
        \Magento\Customer\Model\Session $session
    ) {
        $this->session = $session;
    }

    /**
     * @param Register $subject
     * @param \Magento\Framework\DataObject|null $data
     * @return \Magento\Framework\DataObject|null
     */
    public function afterGetFormData(Register $subject, $data)
    {
        $userData = $this->session->getUserProfile();
        if ($data && $userData) {
            $data->addData(
                ['firstname' => $userData->firstName, 'lastname' => $userData->lastName, 'email' => $userData->email]
            );
        }

        return $data;
    }
}
