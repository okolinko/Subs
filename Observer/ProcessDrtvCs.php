<?php
namespace Toppik\Subscriptions\Observer;

class ProcessDrtvCs implements \Magento\Framework\Event\ObserverInterface {
	
    /**
     * @var \Toppik\Subscriptions\Processor\ProcessDrtvCs
     */
    private $model;
	
    /**
     * ProcessProfiles constructor.
     * @param \Toppik\Subscriptions\Processor\ProcessDrtvCs $model
     */
    public function __construct(
		\Toppik\Subscriptions\Processor\ProcessDrtvCs $model
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
