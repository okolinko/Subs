<?php
namespace Toppik\Subscriptions\Model\Profile;

class Add extends \Magento\Framework\Model\AbstractModel {
    
    const STATUS_DISABLED   = 0;
    const STATUS_ENABLED    = 1;
    
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Profile\Add');
    }
    
    public function getAvailableStatus() {
        return array(
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        );
    }
    
    public function generatePublicHash() {
        return md5($this->getSku() . '-' . $this->getPrice() . '-' . $this->getQty());
    }
    
}
