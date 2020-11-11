<?php
namespace Toppik\Subscriptions\Processor;

class HistoryAdd {
	
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;
	
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    
    /**
     * @var \Toppik\Subscriptions\Model\Profile\HistoryFactory
     */
    private $historyFactory;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
	
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Toppik\Subscriptions\Model\Profile\HistoryFactory $historyFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->objectManager = $objectManager;
		$this->reportHelper = $reportHelper;
        $this->eventManager = $eventManager;
        $this->historyFactory = $historyFactory;
        $this->_customerSession = $customerSession;
        $this->authSession = $authSession;
    }
	
	public function addRecord($entity, $action_code, $message, $note) {
		try {
            $adminId        = 0;
            $admin_email    = null;
            
            if($this->_customerSession->getAdminId()) {
                $adminId = $this->_customerSession->getAdminId();
            }
            
            if($this->authSession && $this->authSession->getUser() && $this->authSession->getUser()->getId()) {
                $adminId        = $this->authSession->getUser()->getId();
                $admin_email    = $this->authSession->getUser()->getEmail();
            }
            
            $history = $this->historyFactory->create();
            
            $history->setData('profile_id', $entity->getId());
            $history->setData('action_code', $action_code);
            $history->setData('customer_id', $this->_customerSession->getCustomerId());
            $history->setData('admin_id', $adminId);
            $history->setData('ip', (isset($_SERVER['REMOTE_ADDR']) ? ip2long($_SERVER['REMOTE_ADDR']) : null));
            $history->setData('status', $entity->getStatus());
            $history->setData('cc', $entity->getPaymentTokenId());
            $history->setData('qty', $entity->getItemsQty());
            $history->setData('frequency', $entity->getFrequencyTitle());
            $history->setData('next_order_at', $entity->getNextOrderAt());
            $history->setData('customer_email', $this->_customerSession->getCustomer()->getData('email'));
            $history->setData('admin_email', $admin_email);
            $history->setData('message', $message);
            $history->setData('note', $note);
            $history->setData('last_suspend_error', $entity->getLastSuspendError());
            
            $history->save();
		} catch(\Exception $e) {
			$message = sprintf('Error during processing subscription_history: %s', $e->getMessage());
			
			$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
			
			$this->eventManager->dispatch(
				'toppikreport_system_add_message',
				['entity_type' => 'subscription_history', 'entity_id' => $entity->getId(), 'message' => $message]
			);
		}
	}
    
}
