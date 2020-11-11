<?php
namespace Toppik\Subscriptions\Processor;

class SubscriptionUpdateCardBillingAddress {
	
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;
	
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->objectManager = $objectManager;
		$this->reportHelper = $reportHelper;
        $this->eventManager = $eventManager;
        $this->_customerSession = $customerSession;
    }
	
	public function execute($token, $address) {
		try {
            if($token && $token->getId()) {
                $collection = $this->objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
                
                $collection
                    ->addFieldToFilter(\Toppik\Subscriptions\Model\Profile::CUSTOMER_ID, $token->getCustomerId())
                    ->addFieldToFilter(\Toppik\Subscriptions\Model\Profile::PAYMENT_TOKEN_ID, $token->getId());
                
                foreach($collection->getItems() as $profile) {
                    $updated    = false;
                    $messages   = array();
                    $billing    = $profile->getBillingAddress();
                    
                    foreach($address->getData() as $_key => $_value) {
                        if(is_array($_value)) {
                            $_value = trim(implode("\n", $_value));
                        }
                        
                        if(is_scalar($_value)) {
                            if($billing->hasData($_key) && $billing->getData($_key) != $_value) {
                                $messages[] = __('Billing %1 changed from "%2" to "%3"', $_key, $billing->getData($_key), $_value);
                                $billing->setData($_key, $_value);
                                $updated = true;
                            }
                        }
                    }
                    
                    if($updated === true) {
                        if(count($messages)) {
                            $profile->setStatusHistoryCode('address_change');
                            $profile->setStatusHistoryNote(__('Credit Card Update'));
                            $profile->setStatusHistoryMessage(implode(', ', $messages));
                        }
                        
                        $profile->save();
                    }
                }
            }
		} catch(\Exception $e) {
			$message = sprintf('Error during processing subscription_update_billing_address: %s', $e->getMessage());
			
			$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
			
			$this->eventManager->dispatch(
				'toppikreport_system_add_message',
				['entity_type' => 'subscription_update_billing_address', 'entity_id' => null, 'message' => $message]
			);
		}
	}
    
}
