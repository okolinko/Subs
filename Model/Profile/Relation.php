<?php
namespace Toppik\Subscriptions\Model\Profile;

class Relation implements \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface {
    
    /**
     * Process object relations
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object) {
        /**
         * @var $object \Magento\Quote\Model\Quote
         */
        if($object->addressCollectionWasSet()) {
            $object->getAddressesCollection()->save();
        }
        
        if($object->itemsCollectionWasSet()) {
            $object->getItemsCollection()->save();
        }
    }
    
}
