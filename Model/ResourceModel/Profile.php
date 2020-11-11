<?php
namespace Toppik\Subscriptions\Model\ResourceModel;

use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order;
use Migration\Step\CustomCustomerAttributes\Data;
use Toppik\Subscriptions\Model\Profile as ProfileModel;

class Profile extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
    
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('subscriptions_profiles', 'profile_id');
    }
    
    /**
     * @param ProfileModel $profile
     * @param Order $order
     */
    public function addOrderRelation(ProfileModel $profile, Order $order) {
        $tableName = $this->getTable('subscriptions_profiles_orders');
        
        $this->getConnection()
            ->query('INSERT INTO `' . $tableName . '` (`profile_id`, `order_id`) VALUES (:profile_id, :order_id)', [
                'profile_id' => $profile->getId(),
                'order_id' => $order->getId(),
            ]);
    }
    
    public function getNumberOfCreatedOrders(ProfileModel $profile) {
        $tableName = $this->getTable('subscriptions_profiles_orders');
        
        return (int) $this->getConnection()
            ->fetchOne('SELECT COUNT(*) FROM `' . $tableName . '` WHERE `profile_id` = :profile_id', [
                'profile_id' => $profile->getId(),
            ]);
    }
    
    /**
     * Process object which was modified
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function processNotModifiedSave(\Magento\Framework\Model\AbstractModel $object) {
        $relations = \Magento\Framework\App\ObjectManager::getInstance()->get('Toppik\Subscriptions\Model\Profile\Relation');
        $relations->processRelation($object);
        return $this;
    }
    
    /**
     * Save data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object) {
        $relations = \Magento\Framework\App\ObjectManager::getInstance()->get('Toppik\Subscriptions\Model\Profile\Relation');
        $relations->processRelation($object);
        return parent::_afterSave($object);
    }
    
    /**
     * Prepare data for save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function prepareDataForSave(\Magento\Framework\Model\AbstractModel $object) {
        return $this->_prepareDataForTable($object, $this->getMainTable());
    }
    
}
