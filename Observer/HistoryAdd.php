<?php
namespace Toppik\Subscriptions\Observer;

class HistoryAdd implements \Magento\Framework\Event\ObserverInterface {
	
    /**
     * @var \Toppik\Subscriptions\Processor\HistoryAdd
     */
    private $model;
	
    /**
     * HistoryAdd constructor.
     * @param \Toppik\Subscriptions\Processor\HistoryAdd $model
     */
    public function __construct(
        \Toppik\Subscriptions\Processor\HistoryAdd $model
    ) {
        $this->model = $model;
    }
	
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $entity         = $observer->getEntity();
        $action_code    = $observer->getActionCode();
        $message        = $observer->getMessage();
        $note           = $observer->getNote();
        
		$this->model->addRecord($entity, $action_code, $message, $note);
    }
	
}
