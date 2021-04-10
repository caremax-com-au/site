<?php
namespace Cia\Caremax\Block;
class Compare extends \Magento\Framework\View\Element\Template
{
	    protected $_storeManager;    

	public function __construct(\Magento\Framework\View\Element\Template\Context $context,        
        \Magento\Store\Model\StoreManagerInterface $storeManager,        
        array $data = [])
	{
		$this->_storeManager = $storeManager;        
		parent::__construct($context);
	}

	    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
 
}