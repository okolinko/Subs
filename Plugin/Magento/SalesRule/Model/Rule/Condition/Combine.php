<?php
namespace Toppik\Subscriptions\Plugin\Magento\SalesRule\Model\Rule\Condition;

class Combine {
    
    public function afterGetNewChildSelectOptions(
        \Magento\SalesRule\Model\Rule\Condition\Combine $original,
        array $conditions
    ) {
        $subscription_conditions = [
            [
                'label' => __('Product Attribute Combination Without Subscription'),
                'value' => 'Toppik\Subscriptions\Model\Rule\Condition\Product\Found'
            ],
            [
                'label' => __('Products Subselection Without Subscription'),
                'value' => 'Toppik\Subscriptions\Model\Rule\Condition\Product\Subselect'
            ]
        ];
        
        $conditions = array_merge_recursive($conditions, $subscription_conditions);
        
        return $conditions;
    }
    
}
