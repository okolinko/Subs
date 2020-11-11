<?php
namespace Toppik\Subscriptions\Model\Rewrite\Magento\SalesRule\Model\Rule\Condition\Product;

class Combine extends \Magento\AdvancedSalesRule\Model\Rule\Condition\Product\Combine {
    
    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct
     * @param \Magento\AdvancedRule\Helper\CombineCondition $conditionHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Toppik\Subscriptions\Model\Rule\Condition\Product\Onetime $ruleConditionProduct,
        \Magento\AdvancedRule\Helper\CombineCondition $conditionHelper,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $conditionHelper, $data);
    }
    
    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions() {
        $productAttributes = $this->_ruleConditionProd->loadAttributeOptions()->getAttributeOption();
        $pAttributes = [];
        $iAttributes = [];
        
        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'quote_item_') === 0) {
                $iAttributes[] = [
                    'value' => 'Toppik\Subscriptions\Model\Rule\Condition\Product\Onetime|' . $code,
                    'label' => $label,
                ];
            } else {
                $pAttributes[] = [
                    'value' => 'Toppik\Subscriptions\Model\Rule\Condition\Product\Onetime|' . $code,
                    'label' => $label,
                ];
            }
        }
        
        $conditions = array_merge_recursive(
            array(),
            [
                [
                    'value' => 'Toppik\Subscriptions\Model\Rewrite\Magento\SalesRule\Model\Rule\Condition\Product\Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Cart Item Attribute'), 'value' => $iAttributes],
                ['label' => __('Product Attribute'), 'value' => $pAttributes]
            ]
        );
        
        return $conditions;
    }
    
}
