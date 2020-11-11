<?php
namespace Toppik\Subscriptions\Observer;

class BeforeProcessProfiles implements \Magento\Framework\Event\ObserverInterface {
	
    /**
     * @var \Toppik\Subscriptions\Processor\BeforeProcessProfiles
     */
    private $model;
	
    /**
     * ProcessProfiles constructor.
     * @param \Toppik\Subscriptions\Processor\BeforeProcessProfiles $model
     */
    public function __construct(
		\Toppik\Subscriptions\Processor\BeforeProcessProfiles $model
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
