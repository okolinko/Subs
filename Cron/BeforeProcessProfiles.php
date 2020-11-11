<?php
namespace Toppik\Subscriptions\Cron;

class BeforeProcessProfiles {
	
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
	
    /**
     * BeforeProcessProfiles constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }
	
    public function execute() {
        $result = new \Magento\Framework\DataObject;
        $this->eventManager->dispatch('subscriptions_profiles_before_process', ['result' => $result]);
    }
	
}
