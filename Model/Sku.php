<?php
namespace Toppik\Subscriptions\Model;

class Sku extends \Magento\Framework\Model\AbstractModel {
	
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Sku');
    }
	
}
