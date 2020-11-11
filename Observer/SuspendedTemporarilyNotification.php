<?php
namespace Toppik\Subscriptions\Observer;

class SuspendedTemporarilyNotification implements \Magento\Framework\Event\ObserverInterface {
	
    /**
     * @var \Toppik\Subscriptions\Processor\SuspendedTemporarilyNotification
     */
    private $model;
	
    /**
     * ProcessProfiles constructor.
     * @param \Toppik\Subscriptions\Processor\SuspendedTemporarilyNotification $model
     */
    public function __construct(
		\Toppik\Subscriptions\Processor\SuspendedTemporarilyNotification $model
    ) {
        $this->model = $model;
    }
	
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $this->model->execute();
    }
	
}
