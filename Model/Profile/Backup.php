<?php
namespace Toppik\Subscriptions\Model\Profile;

class Backup extends \Magento\Framework\Model\AbstractModel {
	
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct() {
        $this->_init('Toppik\Subscriptions\Model\ResourceModel\Profile\Backup');
    }
    
    public function loadByProfile($profile_id) {
        $data = $this->getResource()->loadByProfile($profile_id);
        
        if($data !== false) {
            $this->setData($data);
            $this->setOrigData();
        }
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getBillingAddressJson() {
        return $this->getData('billing_address_json');
    }
    
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getBillingAddress() {
        if(!$this->hasData('billing_address')) {
            $this->setData(
                'billing_address',
                new \Magento\Framework\DataObject(\Zend\Json\Json::decode($this->getBillingAddressJson(), \Zend\Json\Json::TYPE_ARRAY))
            );
        }
        
        return $this->getData('billing_address');
    }
    
    /**
     * @return string|null
     */
    public function getShippingAddressJson() {
        return $this->getData('shipping_address_json');
    }
    
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getShippingAddress() {
        if(!$this->hasData('shipping_address')) {
            $this->setData(
                'shipping_address',
                new \Magento\Framework\DataObject(\Zend\Json\Json::decode($this->getShippingAddressJson(), \Zend\Json\Json::TYPE_ARRAY))
            );
        }
        
        return $this->getData('shipping_address');
    }
    
    /**
     * @return string|null
     */
    public function getItemsJson() {
        return $this->getData('items_json');
    }
    
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getItems() {
        if(!$this->hasData('items')) {
            $this->setData(
                'items',
                new \Magento\Framework\DataObject(\Zend\Json\Json::decode($this->getItemsJson(), \Zend\Json\Json::TYPE_ARRAY))
            );
        }
        
        return $this->getData('items');
    }
    
    /**
     * @return string|null
     */
    public function getQuoteJson() {
        return $this->getData('quote_json');
    }
    
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getQuote() {
        if(!$this->hasData('quote')) {
            $this->setData(
                'quote',
                new \Magento\Framework\DataObject(\Zend\Json\Json::decode($this->getQuoteJson(), \Zend\Json\Json::TYPE_ARRAY))
            );
        }
        
        return $this->getData('quote');
    }
    
    /**
     * @return string|null
     */
    public function getSubscriptionUnitJson() {
        return $this->getData('subscription_unit_json');
    }
    
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getSubscriptionUnit() {
        if(!$this->hasData('subscription_unit')) {
            $this->setData(
                'subscription_unit',
                new \Magento\Framework\DataObject(\Zend\Json\Json::decode($this->getSubscriptionUnitJson(), \Zend\Json\Json::TYPE_ARRAY))
            );
        }
        
        return $this->getData('subscription_unit');
    }
    
    /**
     * @return string|null
     */
    public function getSubscriptionPeriodJson() {
        return $this->getData('subscription_period_json');
    }
    
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getSubscriptionPeriod() {
        if(!$this->hasData('subscription_period')) {
            $this->setData(
                'subscription_period',
                new \Magento\Framework\DataObject(\Zend\Json\Json::decode($this->getSubscriptionPeriodJson(), \Zend\Json\Json::TYPE_ARRAY))
            );
        }
        
        return $this->getData('subscription_period');
    }
    
    /**
     * @return string|null
     */
    public function getSubscriptionItemJson() {
        return $this->getData('subscription_item_json');
    }
    
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getSubscriptionItem() {
        if(!$this->hasData('subscription_item')) {
            $this->setData(
                'subscription_item',
                new \Magento\Framework\DataObject(\Zend\Json\Json::decode($this->getSubscriptionItemJson(), \Zend\Json\Json::TYPE_ARRAY))
            );
        }
        
        return $this->getData('subscription_item');
    }
    
    /**
     * @return string|null
     */
    public function getSubscriptionJson() {
        return $this->getData('subscription_json');
    }
    
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getSubscription() {
        if(!$this->hasData('subscription')) {
            $this->setData(
                'subscription',
                new \Magento\Framework\DataObject(\Zend\Json\Json::decode($this->getSubscriptionJson(), \Zend\Json\Json::TYPE_ARRAY))
            );
        }
        
        return $this->getData('subscription');
    }
    
    /**
     * @param string $billingAddressJson
     * @return $this
     */
    public function setBillingAddressJson($billingAddressJson) {
        $this->unsetData('billing_address');
        return $this->setData('billing_address_json', $billingAddressJson);
    }
    
    /**
     * @param string $shippingAddressJson
     * @return $this
     */
    public function setShippingAddressJson($shippingAddressJson) {
        $this->unsetData('shipping_address');
        return $this->setData('shipping_address_json', $shippingAddressJson);
    }
    
    /**
     * @param string $itemsJson
     * @return $this
     */
    public function setItemsJson($itemsJson) {
        $this->unsetData('items');
        return $this->setData('items_json', $itemsJson);
    }
    
    /**
     * @param string $quoteJson
     * @return $this
     */
    public function setQuoteJson($quoteJson) {
        $this->unsetData('quote');
        return $this->setData('quote_json', $quoteJson);
    }
    
    /**
     * @param string $subscriptionUnitJson
     * @return $this
     */
    public function setSubscriptionUnitJson($subscriptionUnitJson) {
        $this->unsetData('subscription_unit');
        return $this->setData('subscription_unit_json', $subscriptionUnitJson);
    }
    
    /**
     * @param string $subscriptionPeriodJson
     * @return $this
     */
    public function setSubscriptionPeriodJson($subscriptionPeriodJson) {
        $this->unsetData('subscription_period');
        return $this->setData(self::SUBSCRIPTION_PERIOD_JSON, $subscriptionPeriodJson);
    }
    
    /**
     * @param string $subscriptionItemJson
     * @return $this
     */
    public function setSubscriptionItemJson($subscriptionItemJson) {
        $this->unsetData('subscription_item');
        return $this->setData('subscription_item_json', $subscriptionItemJson);
    }
    
    /**
     * @param string $subscriptionJson
     * @return $this
     */
    public function setSubscriptionJson($subscriptionJson) {
        $this->unsetData('subscription');
        return $this->setData('subscription_json', $subscriptionJson);
    }
    
}
