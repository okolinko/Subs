<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
    
    /**
     * @var string
     */
    protected $_idFieldName = 'profile_id';
    
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\Profile', 'Toppik\Subscriptions\Model\ResourceModel\Profile');
    }
    
    /**
     * Changes sku for all profiles
     * @param string $sourceSku
     * @param string $targetSku
     * @return int
     */
    public function changeSku($sourceSku, $targetSku) {
        $total = 0;
        
        if(!empty($sourceSku) && !empty($targetSku)) {
            $collection = array();
            
            $data = $this->getResource()->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION)->fetchAll(
                sprintf(
                    'SELECT DISTINCT profile_id FROM %s WHERE sku = "%s"',
                    $this->getResource()->getTable('subscriptions_profiles_item'),
                    $sourceSku
                )
            );
            
            if(count($data)) {
                foreach($data as $_item) {
                    if(isset($_item['profile_id'])) {
                        $collection[] = $_item['profile_id'];
                    }
                }
            }
            
            if(count($collection)) {
                $this->addFieldToFilter('profile_id', array('in' => $collection));
                
                foreach($this as $profile) {
                    /* @var \Toppik\Subscriptions\Model\Profile $profile */
                    if($profile->changeSku($sourceSku, $targetSku)) {
                        $profile->save();
                        $total ++;
                    }
                }
            }
        }
        
        return $total;
    }
    
}
