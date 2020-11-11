<?php
namespace Toppik\Subscriptions\Model\Profile;

class Item extends \Magento\Framework\Model\AbstractModel {
    
    /**
     * @var Item|null
     */
    protected $_parentItem = null;
    
    /**
     * @var \Toppik\Subscriptions\Model\Profile\Item[]
     */
    protected $_children = [];
    
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Profile\Item');
    }
    
    /**
     * Specify parent item id before saving data
     *
     * @return $this
     */
    public function beforeSave() {
        parent::beforeSave();
        
        if($this->getProfile() && $this->getProfile()->getId()) {
            $this->setProfileId($this->getProfile()->getId());
        }
        
        if($this->getParentItem()) {
            $this->setParentItemId($this->getParentItem()->getId());
        }
        
        return $this;
    }
    
    /**
     * Set parent item
     *
     * @param  \Toppik\Subscriptions\Model\Profile\Item $parentItem
     * @return $this
     */
    public function setParentItem($parentItem) {
        if($parentItem) {
            $this->_parentItem = $parentItem;
            $parentItem->addChild($this);
        }
        
        return $this;
    }
    
    /**
     * Get parent item
     *
     * @return \Toppik\Subscriptions\Model\Profile\Item|null
     */
    public function getParentItem() {
        return $this->_parentItem;
    }
    
    /**
     * Get child items
     *
     * @return \Toppik\Subscriptions\Model\Profile\Item[]
     */
    public function getChildren() {
        return $this->_children;
    }
    
    /**
     * Add child item
     *
     * @param  \Toppik\Subscriptions\Model\Profile\Item $child
     * @return $this
     */
    public function addChild($child) {
        $this->setHasChildren(true);
        $this->_children[] = $child;
        return $this;
    }
    
}
