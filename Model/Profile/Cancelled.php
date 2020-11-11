<?php
namespace Toppik\Subscriptions\Model\Profile;

class Cancelled extends \Magento\Framework\Model\AbstractModel {
    
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Profile\Cancelled');
    }
    
}
