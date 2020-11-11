<?php
namespace Toppik\Subscriptions\Processor;

class BeforeProcessProfiles {
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    protected $_reportHelper;
    
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_objectManager = $objectManager;
        $this->_dateTime = $dateTime;
		$this->_reportHelper = $reportHelper;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
    }
    
    public function execute() {
		try {
            $this->_reportHelper->log('BeforeProcessProfiles -> start');
            
            foreach($this->_storeManager->getStores(false) as $_store) {
                try {
                    $this->_reportHelper->log(sprintf('Start for store ID %s with code %s', $_store->getId(), $_store->getCode()));
                    
                    $this->_storeManager->setCurrentStore($_store->getId());
                    
                    if($this->_reportHelper->getUpcomingOrderMinutes($_store->getId()) > 0) {
                        $profileCollection = $this->_objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
                        
                        $profileCollection
                            ->addFieldToFilter(\Toppik\Subscriptions\Model\Profile::STATUS, array('in' => array(\Toppik\Subscriptions\Model\Profile::STATUS_ACTIVE)))
                            ->addFieldToFilter(\Toppik\Subscriptions\Model\Profile::START_DATE, ['lteq' => $this->_dateTime->gmtDate('Y-m-d')])
                            ->addFieldToFilter('store_id', $_store->getId())
                            ->addFieldToFilter(\Toppik\Subscriptions\Model\Profile::NEXT_ORDER_AT, [
                                [
                                    'lteq' => $this->_dateTime->gmtDate(
                                        'Y-m-d H:i:s',
                                        strtotime(
                                            sprintf(
                                                '%s + %s minute',
                                                $this->_dateTime->gmtDate('Y-m-d H:i:s'),
                                                $this->_reportHelper->getUpcomingOrderMinutes($_store->getId())
                                            )
                                        )
                                    )
                                ]
                            ]);
                        
                        $this->_reportHelper->log(sprintf('Found %s item(s)', count($profileCollection)));
                        $this->_reportHelper->log(sprintf('Current date "%s"', $this->_dateTime->gmtDate('Y-m-d H:i:s')));
                        $this->_reportHelper->log((string) $profileCollection->getSelect());
                        
                        foreach($profileCollection->getItems() as $_profile) {
                            try {
                                $this->_reportHelper->sendUpcomingEmail($_profile);
                            } catch(\Exception $e) {
                                $message = sprintf(
                                    'CANNOT send upcoming email to customer ID %s for profile ID %s: %s',
                                    $_profile->getCustomerId(),
                                    $_profile->getId(),
                                    $e->getMessage()
                                );
                                
                                $this->_reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
                            }
                        }
                        
                        
                    }
                    
                    $this->_reportHelper->log(sprintf('End for store ID %s with code %s', $_store->getId(), $_store->getCode()));
                } catch(\Exception $e) {
                    $message = sprintf(
                        'Error during processing subscriptions_profiles_before_process for store ID %s: %s',
                        $_store->getId(),
                        $e->getMessage()
                    );
                    
                    $this->_reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\WMS\Logger\Logger::ERROR);
                    
                    $this->eventManager->dispatch(
                        'toppikreport_system_add_message',
                        ['entity_type' => 'subscriptions_profiles_before_process', 'entity_id' => null, 'message' => $message]
                    );
                }
            }
            
            $this->_reportHelper->log(sprintf('%s BeforeProcessProfiles -> end', str_repeat('-', 10)));
		} catch(\Exception $e) {
			$message = sprintf('BeforeProcessProfiles: %s', $e->getMessage());
			
			$this->_reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
			
			$this->_eventManager->dispatch(
				'toppikreport_system_add_message',
				[
					'entity_type' 	=> 'subscriptions_profiles_before_process',
					'entity_id' 	=> null,
					'message' 		=> $message
				]
			);
		}
    }
    
}
