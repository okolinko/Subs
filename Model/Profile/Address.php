<?php
namespace Toppik\Subscriptions\Model\Profile;

class Address extends \Magento\Framework\Model\AbstractModel {
    
    const TYPE_BILLING  = 'billing';
    const TYPE_SHIPPING = 'shipping';
    
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Profile\Address');
    }
    
    /**
     * Profile Address Before Save prepare data process
     *
     * @return $this
     */
    public function beforeSave() {
        parent::beforeSave();
        
        if($this->getProfile() && $this->getProfile()->getId()) {
            $this->setProfileId($this->getProfile()->getId());
        }
        
        return $this;
    }
    
}
