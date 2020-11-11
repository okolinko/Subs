<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile;

class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
	const MAIN_TABLE = 'subscriptions_profiles_item';
	
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(self::MAIN_TABLE, 'item_id');
    }
	
}
