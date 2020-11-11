<?php
namespace Toppik\Subscriptions\Observer;

class SystemValidation implements \Magento\Framework\Event\ObserverInterface {
	
    /**
     * @var \Toppik\Subscriptions\Processor\SystemValidation
     */
    private $model;
	
    /**
     * SystemValidation constructor.
     * @param \Toppik\Subscriptions\Processor\SystemValidation $model
     */
    public function __construct(
        \Toppik\Subscriptions\Processor\SystemValidation $model
    ) {
        $this->model = $model;
    }
	
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
		$this->model->validate($observer);
    }
	
}
