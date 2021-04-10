<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CrossLinks
 */


namespace Amasty\CrossLinks\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Link
 * @package Amasty\CrossLinks\Model\ResourceModel
 */
class Link extends AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_cross_link', 'link_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $storeIds = $object->getData('store_ids');
        if ($storeIds && ($storeIds != $object->getOrigData('store_ids'))) {
            if(!is_array($storeIds)) {
                $storeIds = [$storeIds];
            }

            $this->getConnection()->delete($this->getTable('amasty_cross_link_store'),
                ['link_id = ?' => $object->getLinkId()]
            );

            $dataForInsert = [];
            foreach ($storeIds as $storeId) {
                $dataForInsert[] = [
                    'link_id' => $object->getId(),
                    'store_id' => $storeId
                ];
            }
            $this->getConnection()->insertOnDuplicate($this->getTable('amasty_cross_link_store'), $dataForInsert, []);
        }
        return parent::_afterSave($object); // TODO: Change the autogenerated stub
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('amasty_cross_link_store'), 'store_id')
                ->where('link_id = ?', $object->getId());
            $object->setData('store_ids', $this->getConnection()->fetchCol($select));
        }
        return parent::_afterLoad($object);
    }
}
