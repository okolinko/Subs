<?php
namespace Toppik\Subscriptions\Model\Rewrite\Catalog;

class Product extends \Magento\Catalog\Model\Product {
    
    /**
     * Get option from options array of product by given option id
     *
     * @param int $optionId
     * @return Product\Option|null
     */
    public function getOptionById($optionId) {
        
        //file_put_contents(BP . '/var/log/a.log', print_r('', true) . "\n", FILE_APPEND | LOCK_EX);
        //file_put_contents(BP . '/var/log/a.log', print_r(get_class($this), true) . "\n", FILE_APPEND | LOCK_EX);
        //file_put_contents(BP . '/var/log/a.log', print_r($optionId, true) . "\n", FILE_APPEND | LOCK_EX);
        
        /** @var \Magento\Catalog\Model\Product\Option $option */
		if(is_array($this->getData('options'))) { // $this->getData('options') -> $this->getOptions()
			foreach($this->getData('options') as $option) { // $this->getData('options') -> $this->getOptions()
				if($option->getId() == $optionId) {
					return $option;
				}
			}
		}
        
        return null;
    }
    
    /**
     * Check if product has subscription
     *
     * @return bool
     */
    public function getHasSubscription() {
        if(!$this->hasData('has_subscription')) {
            $objectManager      = \Magento\Framework\App\ObjectManager::getInstance();
            $subscriptionHelper = $objectManager->get('\Toppik\Subscriptions\Helper\Data');
            $hasSubscription    = (bool) ($this->getTypeId() == 'combined' || $subscriptionHelper->productHasSubscription($this));
            
            $this->setData('has_subscription', $hasSubscription);
        }
        
        return $this->getData('has_subscription');
    }
    
}
