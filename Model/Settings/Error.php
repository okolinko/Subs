<?php
namespace Toppik\Subscriptions\Model\Settings;

class Error extends \Magento\Framework\Model\AbstractModel {
    
    /**
     * @var array
     */
    protected $_options;
	
    const ERROR_CODE_STOCK                  = 1;
    const ERROR_CODE_CUSTOMER               = 2;
    const ERROR_CODE_PAYMENT_TOKEN          = 3;
    const ERROR_CODE_PAYMENT_TRANSACTION    = 4;
    const ERROR_CODE_FATAL                  = 5;
    const ERROR_CODE_MANUAL_ADMIN           = 6;
    const ERROR_CODE_MANUAL_CUSTOMER        = 7;
    
    const ERROR_REMOVE_CHILD_SUBSCRIPTION   = 100;
    
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions() {
        if($this->_options === null) {
            $this->_options = [
                ['label' => __('Stock Errors'), 'value' => self::ERROR_CODE_STOCK],
                ['label' => __('Customer Errors'), 'value' => self::ERROR_CODE_CUSTOMER],
                ['label' => __('Payment Token Erros'), 'value' => self::ERROR_CODE_PAYMENT_TOKEN],
                ['label' => __('Transaction Errors'), 'value' => self::ERROR_CODE_PAYMENT_TRANSACTION],
                ['label' => __('Fatal/Code Errors'), 'value' => self::ERROR_CODE_FATAL],
                ['label' => __('Manual By Admin'), 'value' => self::ERROR_CODE_MANUAL_ADMIN],
                ['label' => __('Manual By Customer'), 'value' => self::ERROR_CODE_MANUAL_CUSTOMER],
                ['label' => __('Other'), 'value' => 0],
            ];
        }
		
        return $this->_options;
    }
    
    public function toOptionArray() {
        $options = array();
        $options[] = ['label' => '', 'value' => ''];
        
        foreach($this->getAllOptions() as $option) {
            $options[] = ['label' => (string) $option['label'], 'value' => $option['value']];
        }
		
        return $options;
    }
    
}
