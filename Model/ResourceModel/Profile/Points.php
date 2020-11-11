<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile;

class Points extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
	const MAIN_TABLE = 'subscriptions_save_points';
	
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(self::MAIN_TABLE, 'id');
    }
	
}
