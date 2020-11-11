<?php
namespace Toppik\Subscriptions\Cron;

class SuspendedTemporarilyNotification {
	
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
	
    /**
     * SuspendedTemporarilyNotification constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }
	
    public function execute() {
        $result = new \Magento\Framework\DataObject;
        $this->eventManager->dispatch('subscriptions_suspended_temporarily_notification', ['result' => $result]);
    }
	
}
