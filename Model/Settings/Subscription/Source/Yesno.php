<?php
namespace Toppik\Subscriptions\Model\Settings\Subscription\Source;

class Yesno implements \Magento\Framework\Data\OptionSourceInterface {
    
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray() {
        $options = array();
        
        $options[] = ['label' => '', 'value' => ''];
        $options[] = ['label' => __('No'), 'value' => 0];
        $options[] = ['label' => __('Yes'), 'value' => 1];
        
        return $options;
    }
    
}
