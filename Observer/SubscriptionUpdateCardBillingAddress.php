<?php
namespace Toppik\Subscriptions\Observer;

class SubscriptionUpdateCardBillingAddress implements \Magento\Framework\Event\ObserverInterface {
	
    /**
     * @var \Toppik\Subscriptions\Processor\SubscriptionUpdateCardBillingAddress
     */
    private $model;
	
    /**
     * SubscriptionUpdateCardBillingAddress constructor.
     * @param \Toppik\Subscriptions\Processor\SubscriptionUpdateCardBillingAddress $model
     */
    public function __construct(
        \Toppik\Subscriptions\Processor\SubscriptionUpdateCardBillingAddress $model
    ) {
        $this->model = $model;
    }
	
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $token      = $observer->getToken();
        $address    = $observer->getAddress();
        
		$this->model->execute($token, $address);
    }
	
}
