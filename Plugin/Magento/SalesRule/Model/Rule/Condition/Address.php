<?php
namespace Toppik\Subscriptions\Plugin\Magento\SalesRule\Model\Rule\Condition;

class Address {
    
    public function afterLoadAttributeOptions(
        \Magento\SalesRule\Model\Rule\Condition\Address $original
    ) {
        $attributes = $original->getAttributeOption();
        
        if(is_array($attributes)) {
            $subscription_attributes = [
                'base_subtotal_without_subscription'    => __('Subtotal Without Subscription'),
                'total_qty_without_subscription'        => __('Total Items Quantity Without Subscription'),
                'weight_without_subscription'           => __('Total Weight Without Subscription')
            ];
            
            $attributes = array_merge($attributes, $subscription_attributes);
            
            ksort($attributes);
            
            $original->setAttributeOption($attributes);
        }
        
        return $original;
    }
    
}
