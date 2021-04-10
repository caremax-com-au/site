<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Model;

use Amasty\Stripe\Api\CustomerRepositoryInterface;
use Amasty\Stripe\Model\Adapter\StripeAdapter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Stripe\Error\InvalidRequest;
use Stripe\PaymentMethod;

/**
 * Managements for Stripe accounts
 */
class StripeAccountManagement
{
    /**
     * @var Adapter\StripeAdapter
     */
    private $adapter;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    public function __construct(
        StripeAdapter $adapter,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        SessionManagerInterface $session
    ) {
        $this->adapter = $adapter;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->session = $session;
    }

    /**
     * @param array|string $stripeData
     * @param string $action
     *
     * @return string|null
     */
    public function process($stripeData, $action = 'save')
    {
        $account = $this->resolveStripeCustomer();
        if ($account) {
            $accountId = $account->id;

            switch ($action) {
                case 'save':
                    $this->createCard($stripeData[0], $accountId);
                    break;
                case 'save_for_recurring':
                    $paymentMethod = $this->createCard($stripeData[0], $accountId);
                    if ($paymentMethod) {
                        return $paymentMethod;
                    }
                    break;
                case 'delete':
                    $this->deleteCard($stripeData);
                    break;
            }

            return $accountId;
        }
    }

    /**
     * @return array
     */
    public function getAllCards()
    {
        $cardsData = [];

        try {
            /** @var \Amasty\Stripe\Model\Customer $customer */
            $customer = $this->customerRepository->getStripeCustomer(
                $this->session->getCustomerId(),
                $this->adapter->getAccountId()
            );
            $cards = $this->adapter->listOfCards($customer->getStripeCustomerId());

            if ($cards) {
                $cardsData = $cards->data;
            }
        } catch (NoSuchEntityException $exception) {
            return [];
        }

        return $cardsData;
    }

    /**
     * @return \Stripe\Customer
     */
    private function createAccount()
    {
        $customerEmail = $this->session->getQuote()
            ? $this->session->getQuote()->getCustomer()->getEmail()
            : $this->session->getCustomer()->getEmail();
        $accountData = [
            'email' => $customerEmail,
            'description' => 'Magento customer ID is ' . $this->session->getCustomerId()
        ];
        /** @var \Stripe\Customer $account */
        $account = $this->adapter->customerCreate($accountData);
        $accountId = $account->id;
        /** @var \Amasty\Stripe\Model\Customer $customer */
        $customer = $this->customerFactory->create();
        $customer->setCustomerId($this->session->getCustomerId());
        $customer->setStripeCustomerId($accountId);
        $customer->setAccountCustomerId($this->adapter->getAccountId());
        $this->customerRepository->save($customer);

        return $account;
    }

    /**
     * @param string $paymentMethod
     */
    private function deleteCard($paymentMethod)
    {
        $this->adapter->detachPaymentMethod($paymentMethod);
    }

    /**
     * @param string $paymentMethod
     * @param string $accountId
     */
    public function createCard($paymentMethod, $accountId)
    {
        /** @var \Stripe\Source $retrievedSource */
        $retrievedPayment = $this->adapter->paymentRetrieve($paymentMethod);
        $existingPaymentMethod = $this->paymentMethodResolver($retrievedPayment->card->fingerprint);

        if ($existingPaymentMethod) {
            return $existingPaymentMethod;
        }
        if ($retrievedPayment
            && !$this->checkExistCard($retrievedPayment, $accountId)
        ) {
            $retrievedPayment->attach(['customer' => $accountId]);
        }
        return $retrievedPayment;
    }

    /**
     * @param string $fingerprint
     *
     * @return bool|object
     */
    private function paymentMethodResolver($fingerprint)
    {
        $paymentMethods = $this->getAllCards();
        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod->card->fingerprint == $fingerprint) {
                return $paymentMethod;
            }
        }

        return false;
    }

    /**
     * @param \Stripe\Source $paymentMethod
     * @param string $accountId
     *
     * @return bool
     */
    private function checkExistCard(PaymentMethod $paymentMethod, $accountId)
    {
        /** @var \Stripe\ApiResource $allCards */
        $allCards = $this->adapter->listOfCards($accountId);
        $isExist = false;

        $fingerPrint = $this->getFingerPrint($paymentMethod);

        if (!$fingerPrint) {
            return $isExist;
        }

        if ($allCards && $allCards->data) {
            /** @var \Stripe\Source $card */
            foreach ($allCards->data as $existCard) {
                $existFingerPrint = $this->getFingerPrint($existCard);

                if ($existFingerPrint === $fingerPrint) {
                    $isExist = true;
                    break;
                }
            }
        }

        return $isExist;
    }

    /**
     * @param string $source
     *
     * @return string|null
     */
    private function getFingerPrint($paymentMethod)
    {
        if ($paymentMethod->three_d_secure) {
            $fingerPrint = $paymentMethod->three_d_secure->fingerprint;
        } elseif ($paymentMethod->card) {
            $fingerPrint = $paymentMethod->card->fingerprint;
        } else {
            $fingerPrint = $paymentMethod->fingerprint;
        }

        return $fingerPrint;
    }

    /**
     * Return Stripe Customer Id associated with current Customer.
     * If no associated stripe customer then create new
     *
     * @return \Stripe\Customer|null
     */
    public function resolveStripeCustomer()
    {
        if (!$customerId = $this->session->getCustomerId()) {
            return null;
        }

        try {
            /** @var \Amasty\Stripe\Model\Customer $customer */
            $customer = $this->customerRepository->getStripeCustomer(
                $customerId,
                $this->adapter->getAccountId()
            );
            $customerId = $customer->getStripeCustomerId();
            $account = $this->adapter->customerRetrieve($customerId);
            if ($account->isDeleted()) {
                throw new LocalizedException(__('Stripe account was deleted'));
            }
        } catch (NoSuchEntityException $exception) {
            /** @var \Stripe\Customer $account */
            $account = $this->createAccount();
        } catch (LocalizedException $exception) {
            $this->customerRepository->delete($customer);
            /** @var \Stripe\Customer $account */
            $account = $this->createAccount();
        } catch (\Stripe\Exception\InvalidRequestException $stripeException) {
            $this->customerRepository->delete($customer);
            /** @var \Stripe\Customer $account */
            $account = $this->createAccount();
        } catch (InvalidRequest $exception) {
            $this->customerRepository->delete($customer);
            /** @var \Stripe\Customer $account */
            $account = $this->createAccount();
        }

        return $account;
    }

    /**
     * Get current customer ID For Stripe
     *
     * @return \Stripe\Customer|null
     */
    public function getCurrentStripeCustomerId()
    {
        return $this->resolveStripeCustomer();
    }
}
