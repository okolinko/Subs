<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile;

class Add extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
	const MAIN_TABLE = 'subscriptions_rules_add';
	
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(self::MAIN_TABLE, 'id');
    }
	
}
