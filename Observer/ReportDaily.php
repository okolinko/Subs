<?php
namespace Toppik\Subscriptions\Observer;

class ReportDaily implements \Magento\Framework\Event\ObserverInterface {
	
    /**
     * @var \Toppik\Subscriptions\Processor\ReportDaily
     */
    private $model;
	
    /**
     * ProcessProfiles constructor.
     * @param \Toppik\Subscriptions\Processor\ReportDaily $model
     */
    public function __construct(
		\Toppik\Subscriptions\Processor\ReportDaily $model
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
