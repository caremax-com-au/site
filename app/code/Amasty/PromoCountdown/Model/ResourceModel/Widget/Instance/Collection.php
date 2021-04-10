<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PromoCountdown
 */


namespace Amasty\PromoCountdown\Model\ResourceModel\Widget\Instance;

/**
 * Class Collection.
 * Necessary for the correct working of the Magento\Ui\Component\MassAction\Filter component.
 * @used-by \Amasty\PromoCountdown\Controller\Adminhtml\Instance\MassDelete
 */
class Collection extends \Magento\Widget\Model\ResourceModel\Widget\Instance\Collection
{
    protected function _construct()
    {
        parent::_construct();

        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
