<?php
namespace Toppik\Subscriptions\Processor;

class SystemValidation {
	
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
     * @var ManagerInterface
     */
    private $eventManager;
    
    /**
     * @var ResourceConnection
     */
    private $resource;
	
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->objectManager = $objectManager;
        $this->dateTime = $dateTime;
		$this->reportHelper = $reportHelper;
        $this->eventManager = $eventManager;
        $this->resource = $resource;
    }
	
	public function validate(\Magento\Framework\Event\Observer $observer = null) {
        $this->_validateActiveProfiles();
        $this->_validateDrtvAdminOrders();
	}
    
    protected function _validateActiveProfiles() {
		try {
			$this->reportHelper->log('SystemValidation ActiveProfiles - > start', []);
            
            $collection = array();
            
            $data = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION)->fetchAll(
                sprintf(
                    'SELECT p.profile_id AS entity_id
                    FROM %s AS p
                    WHERE p.status = "%s" AND (p.next_order_at IS NULL || TIMESTAMPDIFF(HOUR, p.next_order_at, NOW()) > 24)',
                    $this->resource->getTableName('subscriptions_profiles'),
                    \Toppik\Subscriptions\Model\Profile::STATUS_ACTIVE
                )
            );
            
            if(count($data)) {
                foreach($data as $_item) {
                    $collection[] = $_item['entity_id'];
                }
            }
            
            if(count($collection) > 0) {
                $message = sprintf(
                                implode(
                                    ' -> ',
                                    array(
                                        'SystemValidation',
                                        'ActiveProfiles',
                                        'Found %s active subscription(s) which wasn\'t processed over the past 24 hours',
                                        'Autoship order(s) hasn\'t been created yet',
                                        'subscription IDs: %s'
                                    )
                                ),
                                count($collection),
                                count($collection) <= 100 ? implode(', ', $collection) : 'to big to be displayed'
                           );
                
                $this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
                
                $this->eventManager->dispatch(
                    'toppikreport_system_add_message',
                    [
                        'entity_type'   => 'subscription_validation',
                        'entity_id'     => null,
                        'message'       => $message
                    ]
                );
            }
            
			$this->reportHelper->log(sprintf('%s SystemValidation ActiveProfiles -> end', str_repeat('-', 10)));
		} catch(\Exception $e) {
			$message = sprintf('Error during processing subscription_validation ActiveProfiles: %s', $e->getMessage());
			
			$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
			
			$this->eventManager->dispatch(
				'toppikreport_system_add_message',
				['entity_type' => 'subscription_validation', 'entity_id' => null, 'message' => $message]
			);
		}
    }
    
    protected function _validateDrtvAdminOrders() {
		try {
			$this->reportHelper->log('SystemValidation DrtvAdminOrders - > start', []);
            
            $collection = array();
            
            $data = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION)->fetchAll(
                sprintf(
                    'SELECT o.increment_id AS entity_id
                    FROM %s AS oi
                    INNER JOIN %s AS o ON o.entity_id = oi.order_id
                    WHERE o.admin_id IS NOT NULL AND o.state IN("complete") AND oi.sku LIKE "%%DRTV%%" AND o.processed_drtv_cs = 0
                    AND TIMESTAMPDIFF(HOUR, IF(ISNULL(o.completed_at) || UNIX_TIMESTAMP(o.completed_at) = 0, o.created_at, o.completed_at), NOW()) > 24',
                    $this->resource->getTableName('sales_order_item'),
                    $this->resource->getTableName('sales_order')
                )
            );
            
            if(count($data)) {
                foreach($data as $_item) {
                    $collection[] = $_item['entity_id'];
                }
            }
            
            if(count($collection) > 0) {
                $message = sprintf(
                                implode(
                                    ' -> ',
                                    array(
                                        'SystemValidation',
                                        'DrtvAdminOrders',
                                        'Found %s DRTV order(s) created by admin which wasn\'t processed over the past 24 hours',
                                        'New profile(s) hasn\'t been created yet',
                                        'order IDs: %s'
                                    )
                                ),
                                count($collection),
                                count($collection) <= 100 ? implode(', ', $collection) : 'to big to be displayed'
                           );
                
                $this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
                
                $this->eventManager->dispatch(
                    'toppikreport_system_add_message',
                    [
                        'entity_type'   => 'subscription_validation',
                        'entity_id'     => null,
                        'message'       => $message
                    ]
                );
            }
            
			$this->reportHelper->log(sprintf('%s SystemValidation DrtvAdminOrders -> end', str_repeat('-', 10)));
		} catch(\Exception $e) {
			$message = sprintf('Error during processing subscription_validation DrtvAdminOrders: %s', $e->getMessage());
			
			$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
			
			$this->eventManager->dispatch(
				'toppikreport_system_add_message',
				['entity_type' => 'subscription_validation', 'entity_id' => null, 'message' => $message]
			);
		}
    }
    
}
