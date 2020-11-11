<?php
namespace Toppik\Subscriptions\Block\Customer\Account\View;

class Payments extends \Magento\Framework\View\Element\Template {
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
		$this->registry = $registry;
        parent::__construct($context, $data);
    }
    
    public function getProfile() {
		return $this->registry->registry('current_profile');
    }
    
    public function getInfoBoxTitle() {
        return __('Subscription Payments');
    }
    
    public function getInfoBoxFields() {
        $profile = $this->getProfile();
        
		$fields = array(
            array(
                'title' => __('Currency:'),
                'value' => $profile->getCurrencyCode()
            ),
            
            array(
                'title' => __('Billing Amount:'),
                'value' => $profile->getBillingAmount()
            ),
            
            array(
                'title' => __('Shipping Amount:'),
                'value' => $profile->getShippingAmount()
            ),
            
            /* array(
                'title' => __('Tax Amount:'),
                'value' => $profile->getTaxAmount()
            ) */
		);
		
		return $fields;
    }
    
}
