<?php
namespace Toppik\Subscriptions\Processor;

class SuspendedTemporarilyNotification {
	
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
	
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;
    
    /**
     * @var ResourceConnection
     */
    private $resource;
    
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    
    /**
     * @var TimezoneInterface
     */
    protected $timezone;
    
    /**
     * ActiveProfiles constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Toppik\Subscriptions\Converter\QuoteToProfile $quoteToProfile
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->objectManager = $objectManager;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
		$this->reportHelper = $reportHelper;
        $this->resource = $resource;
        $this->eventManager = $eventManager;
    }

    public function execute() {
		try {
			$this->reportHelper->log("SuspendedTemporarilyNotification - > start", []);
            
            $days = $this->reportHelper->getSuspendedTemporarilyNotificationDays();
            
            if($days < 1) {
                throw new \Exception(__('Number of days is not specified.'));
            }
            
            /* @var \Toppik\Subscriptions\Model\ResourceModel\Profile\Collection $profileCollection */
            $profileCollection = $this->objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
            
            $profileCollection
                ->addFieldToFilter(\Toppik\Subscriptions\Model\Profile::STATUS, \Toppik\Subscriptions\Model\Profile::STATUS_SUSPENDED_TEMPORARILY)
                ->addFieldToFilter(\Toppik\Subscriptions\Model\Profile::NEXT_ORDER_AT, [
                    ['lteq' => $this->dateTime->gmtDate('Y-m-d H:i:s', ($this->dateTime->gmtTimestamp() + ($days * 60 * 60 * 24)))]
                ]);
            
            $this->reportHelper->log(sprintf('%s Found %s profile(s)', str_repeat('-', 5), count($profileCollection->getItems())), []);
            
            foreach($profileCollection->getItems() as $_profile) {
                $this->_process($_profile);
            }

			$this->reportHelper->log(sprintf('%s SuspendedTemporarilyNotification -> end', str_repeat('-', 10)));
		} catch(\Exception $e) {
			$message = sprintf('Error during processing subscriptions_suspended_temporarily_notification: %s', $e->getMessage());
			
			$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
			
			$this->eventManager->dispatch(
				'toppikreport_system_add_message',
				['entity_type' => 'subscriptions_suspended_temporarily_notification', 'entity_id' => null, 'message' => $message]
			);
		}
    }
    
	protected function _process($profile) {
        try {
            $title  = array();
            $sku    = array();
            
            foreach($profile->getAllVisibleItems() as $_item) {
                if((int) $_item->getData('is_onetime_gift') !== 1) {
                    $title[] = $_item->getName();
                    $sku[] = $_item->getSku();
                }
            }

            $template = $this->reportHelper->getSuspendedTemporarilyNotificationEmailTemplate();
            
            $vars = array(
                'profile'   => $profile,
                'customer'  => $profile->getCustomer(),
                'next_date' => date('m/d/Y', strtotime($profile->getNextOrderAt())),
                'sku'       => implode(', ', $sku),
                'title'     => implode(', ', $title)
            );
            
            $this->reportHelper->sendEmail($template, $profile->getCustomer()->getEmail(), $profile->getStoreId(), $vars);
        } catch(\Exception $e) {
			$message = sprintf(
                'Error during processing subscriptions_suspended_temporarily_notification for profile ID %s: %s',
                $profile->getId(),
                $e->getMessage()
            );
            
			$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
			
			$this->eventManager->dispatch(
				'toppikreport_system_add_message',
				['entity_type' => 'subscriptions_suspended_temporarily_notification', 'entity_id' => $profile->getId(), 'message' => $message]
			);
        }
	}
    
}
