<?php
/**
 * MageVision Free Shipping Admin Extension
 *
 * @category     MageVision
 * @package      MageVision_FreeShippingAdmin
 * @author       MageVision Team
 * @copyright    Copyright (c) 2020 MageVision (http://www.magevision.com)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace MageVision\FreeShippingAdmin\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Framework\App\State;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Model\Rate\Result;
use Magento\Quote\Model\Quote\Address\RateResult\Method as RateResultMethod;
use Magento\Store\Model\StoreManagerInterface;
class Method extends AbstractCarrier implements CarrierInterface
{
   
    private $storeManager;
    /**
     * @var string
     */
    protected $_code = 'freeshippingadmin';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param State $appState
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        State $appState,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $data
        );
    }

    /**
     * Checks if user is logged in as admin
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function isAdmin(): bool
    {
        if ($this->appState->getAreaCode() === FrontNameResolver::AREA_CODE) {
            return true;
        }
        return false;
    }
    
    /**
     * FreeShipping Rates Collector
     *
     * @param RateRequest $request
     * @return Result|bool
     * @throws LocalizedException
     */
    public function collectRates(RateRequest $request)
    {
       
        $current_url_without_param = current(explode("?", $this->storeManager->getStore()->getCurrentUrl()));
        $base_url = $this->storeManager->getStore()->getBaseUrl();
        if ($this->getConfigFlag('active') && $this->appState->getAreaCode() === 'crontab' || $this->getConfigFlag('active') && $this->appState->getAreaCode() === 'adminhtml' || $this->getConfigFlag('active') && $current_url_without_param  === $base_url) {
           /** @var Result $result */
        $result = $this->rateResultFactory->create();

        /** @var RateResultMethod $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier('freeshippingadmin');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('freeshippingadmin');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice('0.00');
        $method->setCost('0.00');

        $result->append($method);

        return $result;
            
        }else {
            return false;
        }

        
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return ['freeshippingadmin' => $this->getConfigData('name')];
    }
}
