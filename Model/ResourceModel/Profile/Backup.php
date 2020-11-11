<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile;

class Backup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
	const MAIN_TABLE = 'subscriptions_profiles_backup';
	
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(self::MAIN_TABLE, 'id');
    }
	
    public function loadByProfile($profile_id) {
        $select = $this->getConnection()->select()->from($this->getMainTable())->where('profile_id=:profile_id');
        return $this->getConnection()->fetchRow($select, [':profile_id' => $profile_id]);
    }
    
}
