<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile\Address;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    /**
     * @var string
     */
    protected $_idFieldName = 'address_id';
    
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\Profile\Address', 'Toppik\Subscriptions\Model\ResourceModel\Profile\Address');
    }
    
    public function setProfileFilter($profile_id) {
        $this->addFieldToFilter('profile_id', $profile_id);
        return $this;
    }
	
}
