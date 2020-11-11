<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile\Item;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';
    
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\Profile\Item', 'Toppik\Subscriptions\Model\ResourceModel\Profile\Item');
    }
    
    /**
     * Set parent items
     *
     * @return $this
     */
    protected function _afterLoad() {
        parent::_afterLoad();
        
        /**
         * Assign parent items
         */
        foreach($this as $item) {
            if($item->getParentItemId()) {
                $item->setParentItem($this->getItemById($item->getParentItemId()));
            }
        }
        
        return $this;
    }
    
    public function setProfileFilter($profile_id) {
        $this->addFieldToFilter('profile_id', $profile_id);
        return $this;
    }
	
}
