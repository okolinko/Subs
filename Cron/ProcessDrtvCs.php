<?php
namespace Toppik\Subscriptions\Cron;

class ProcessDrtvCs {
	
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
	
    /**
     * ProcessDrtvCs constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }
	
    public function execute() {
        $result = new \Magento\Framework\DataObject;
        $this->eventManager->dispatch('subscriptions_process_drtv_cs', ['result' => $result]);
    }
	
}
