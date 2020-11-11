<?php
namespace Toppik\Subscriptions\Cron;

class ReportDaily {
	
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
	
    /**
     * ReportDaily constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }
	
    public function execute() {
        $result = new \Magento\Framework\DataObject;
        $this->eventManager->dispatch('subscriptions_report_daily', ['result' => $result]);
    }
	
}
