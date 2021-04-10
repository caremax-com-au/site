<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model;

use Amasty\Customform\Helper\Messages;
use Magento\Customer\Model\Context;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\App\Area;

class VariablesSetter
{
    const CUSTOMER_FIELDS = [
        Messages::FIRST_NAME,
        Messages::LAST_NAME,
        Messages::CITY,
        Messages::COMPANY,
        Messages::EMAIL,
        Messages::FAX,
        Messages::PHONE_NUMBER,
        Messages::POST_CODE,
        Messages::REGION,
        Messages::STREET_ADDRESS,
    ];

    /**
     * @var CustomerSessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    public function __construct(
        CustomerSessionFactory $customerSessionFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\State $state
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->httpContext = $httpContext;
        $this->state = $state;
    }

    /**
     * @param string $data
     * @param bool $isNeedClean
     * @return string
     */
    public function replaceVariables($data, $isNeedClean = false)
    {
        if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
            return $data;
        }

        $isLogged = $this->httpContext->getValue(Context::CONTEXT_AUTH);
        $customer = null;
        foreach (self::CUSTOMER_FIELDS as $fieldName) {
            if (strpos($data, $fieldName) !== false) {
                if ($isLogged && !$isNeedClean) {
                    $customer = $customer ?: $this->customerSessionFactory->create()->getCustomer();
                    $value = $this->getCustomerValue($customer, $fieldName);
                    $data = str_replace($fieldName, $value, $data);
                } else {
                    $data = $this->clearField($isNeedClean, $fieldName, $data);
                }
            }
        }

        return $data;
    }

    /**
     * @param bool $isNeedClean
     * @param string $fieldName
     * @param string $data
     * @return string
     */
    private function clearField($isNeedClean, $fieldName, $data)
    {
        $value = '';
        if ($isNeedClean) {
            $value = sprintf('"%s"', $value);
            $regex = sprintf('/"[^"]*%s[^"]*"/', $fieldName);
            $data = preg_replace($regex, $value, $data);
        } else {
            $data = $value;
        }

        return $data;
    }

    /**
     * @param Customer $customer
     * @param string $fieldName
     * @return string
     */
    private function getCustomerValue($customer, $fieldName)
    {
        $fieldCode = trim($fieldName, '{}');
        $addressFields = [
            Messages::COMPANY,
            Messages::CITY,
            Messages::POST_CODE,
            Messages::REGION,
            Messages::FAX,
            Messages::STREET_ADDRESS,
            Messages::PHONE_NUMBER,
        ];
        $value = in_array($fieldName, $addressFields)
            ? $this->getAddressValue($customer, $fieldName, $fieldCode)
            : $customer->getData($fieldCode);

        return $value ?: '';
    }

    /**
     * @param Customer $customer
     * @param string $fieldName
     * @param string $fieldCode
     * @return string
     */
    private function getAddressValue($customer, $fieldName, $fieldCode)
    {
        $address = $customer->getDefaultBillingAddress();
        if (!$address) {
            return '';
        }

        switch ($fieldName) {
            case Messages::STREET_ADDRESS:
                $value = preg_replace('/[\n]/', ' - ', $address->getData($fieldCode));
                break;
            case Messages::PHONE_NUMBER:
                $value = preg_replace("/[^0-9]/", "", $address->getData($fieldCode));
                break;
            default:
                $value = $address->getData($fieldCode);
        }

        return $value;
    }
}
