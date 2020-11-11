<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile;

class Save extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
	
	const MAIN_TABLE = 'subscriptions_save';
	
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(self::MAIN_TABLE, 'id');
    }
	
    public function getLifetimeUsedPoints($profile_id) {
        $value = 0;
        
        if((int) $profile_id > 0) {
            $value = (int) $this->getConnection()->fetchOne(
                sprintf(
                    'SELECT SUM(used_points) AS value FROM %s WHERE profile_id = %s',
                    $this->getConnection()->getTableName('subscriptions_save'),
                    (int) $profile_id
                )
            );
        }
        
        return max(0, $value);
    }
    
    public function getOnetimeCouponCode($profile_id) {
        $value = null;
        
        if((int) $profile_id > 0) {
            $value = $this->getConnection()->fetchOne(
                sprintf(
                    'SELECT
                        value   
                    FROM %s
                    WHERE
                        profile_id = %s
                        AND option_id IN (SELECT id FROM %s WHERE type_id = %s)
                    ORDER BY id DESC
                    LIMIT 1',
                    $this->getConnection()->getTableName('subscriptions_save'),
                    (int) $profile_id,
                    $this->getConnection()->getTableName('subscriptions_save_points'),
                    \Toppik\Subscriptions\Model\Profile\Points::TYPE_COUPON
                )
            );
            
            if(!$value || empty($value)) {
                $value = null;
            }
        }
        
        return $value;
    }
    
}
