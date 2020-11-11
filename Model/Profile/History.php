<?php
namespace Toppik\Subscriptions\Model\Profile;

class History extends \Magento\Framework\Model\AbstractModel {
	
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Profile\History');
    }
	
}
