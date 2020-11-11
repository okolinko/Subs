<?php
namespace Toppik\Subscriptions\Processor;

class ReportDaily {
	
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
			$this->reportHelper->log("ReportDaily - > start", []);
            $this->_process();
			$this->reportHelper->log(sprintf('%s ReportDaily -> end', str_repeat('-', 10)));
		} catch(\Exception $e) {
			$message = sprintf('Error during processing subscription_report_daily: %s', $e->getMessage());
			
			$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
			
			$this->eventManager->dispatch(
				'toppikreport_system_add_message',
				['entity_type' => 'subscription_report_daily', 'entity_id' => null, 'message' => $message]
			);
		}
    }
	
    public function processByRange($from, $to) {
        $from   = new \DateTime($from);
        $to     = new \DateTime($to);
        
        // echo sprintf('%s - %s', $from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'));exit;
        
        if($from < $to) {
            do {
                $this->_process($from);
                $from->add(new \DateInterval('P1D'));
            } while($from <= $to);
        }
    }
    
	protected function _process($date = null) {
        try {
            $data           = array();
            $reportTable 	= $this->resource->getTableName('subscription_report_daily');
            $profileTable   = $this->resource->getTableName('subscriptions_profiles');
            
            if($date === null) {
                $date = new \DateTime($this->dateTime->gmtDate());
                
                $date->setTime(0, 0, 0);
                
                $from   = new \DateTime($date->format('Y-m-d H:i:s'));
                $to 	= new \DateTime($date->format('Y-m-d H:i:s'));
                
                $to->add(new \DateInterval('PT23H59M59S'));
                
                $data = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION)->fetchAll(
                    sprintf(
                        'SELECT
                        (SELECT COUNT(profile_id) FROM %s WHERE `status` = "active" AND merchant_source = "toppik.com" GROUP BY `status`)
                        AS active,
                        (SELECT COUNT(profile_id) FROM %s WHERE `status` = "active" AND merchant_source != "toppik.com" GROUP BY `status`)
                        AS active_ms,
                        (SELECT COUNT(profile_id) FROM %s WHERE `status` = "suspended" AND merchant_source = "toppik.com" GROUP BY `status`)
                        AS suspended,
                        (SELECT COUNT(profile_id) FROM %s WHERE `status` = "suspended" AND merchant_source != "toppik.com" GROUP BY `status`)
                        AS suspended_ms,
                        (SELECT COUNT(profile_id) FROM %s WHERE `status` = "cancelled" AND merchant_source = "toppik.com" GROUP BY `status`)
                        AS cancelled,
                        (SELECT COUNT(profile_id) FROM %s WHERE `status` = "cancelled" AND merchant_source != "toppik.com" GROUP BY `status`)
                        AS cancelled_ms',
                        $profileTable,
                        $profileTable,
                        $profileTable,
                        $profileTable,
                        $profileTable,
                        $profileTable
                    )
                );
            } else {
                $date->setTime(0, 0, 0);
                
                $from   = new \DateTime($date->format('Y-m-d H:i:s'));
                $to 	= new \DateTime($date->format('Y-m-d H:i:s'));
                
                $to->add(new \DateInterval('PT23H59M59S'));
                
                $data = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION)->fetchAll(
                    sprintf(
                        'SELECT
                        
                        "%s - %s" AS period,
                        
                        (
                            SELECT COUNT(profile_id)
                            FROM %s
                            WHERE
                                (
                                    (
                                        `status` = "active"
                                        AND (created_at <= "%s")
                                    )
                                    ||
                                    (
                                        `status` != "active"
                                        AND (
                                            (created_at <= "%s")
                                            AND (
                                                (
                                                    cancelled_at IS NOT NULL AND cancelled_at > "%s"
                                                )
                                                ||
                                                (
                                                    suspended_at IS NOT NULL AND suspended_at > "%s"
                                                )
                                            )
                                        )
                                    )
                                )
                                AND merchant_source = "toppik.com"
                        ) AS active,
                        
                        (
                            SELECT COUNT(profile_id)
                            FROM %s
                            WHERE
                                (
                                    (
                                        `status` = "active"
                                        AND (created_at <= "%s")
                                    )
                                    ||
                                    (
                                        `status` != "active"
                                        AND (
                                            (created_at <= "%s")
                                            AND (
                                                (
                                                    cancelled_at IS NOT NULL AND cancelled_at > "%s"
                                                )
                                                ||
                                                (
                                                    suspended_at IS NOT NULL AND suspended_at > "%s"
                                                )
                                            )
                                        )
                                    )
                                )
                                AND merchant_source != "toppik.com"
                        ) AS active_ms,
                        
                        (
                            SELECT COUNT(profile_id)
                            FROM %s
                            WHERE
                                (
                                    (
                                        `status` = "cancelled"
                                        AND (created_at <= "%s")
                                        AND (cancelled_at IS NOT NULL AND cancelled_at <= "%s")
                                    )
                                )
                                AND merchant_source = "toppik.com"
                        ) AS cancelled,
                        
                        (
                            SELECT COUNT(profile_id)
                            FROM %s
                            WHERE
                                (
                                    (
                                        `status` = "cancelled"
                                        AND (created_at <= "%s")
                                        AND (cancelled_at IS NOT NULL AND cancelled_at <= "%s")
                                    )
                                )
                                AND merchant_source != "toppik.com"
                        ) AS cancelled_ms,
                        
                        (
                            SELECT COUNT(profile_id)
                            FROM %s
                            WHERE
                                (
                                    (
                                        `status` = "suspended"
                                        AND (created_at <= "%s")
                                        AND (suspended_at IS NOT NULL AND suspended_at <= "%s")
                                    )
                                )
                                AND merchant_source = "toppik.com"
                        ) AS suspended,
                        
                        (
                            SELECT COUNT(profile_id)
                            FROM %s
                            WHERE
                                (
                                    (
                                        `status` = "suspended"
                                        AND (created_at <= "%s")
                                        AND (suspended_at IS NOT NULL AND suspended_at <= "%s")
                                    )
                                )
                                AND merchant_source != "toppik.com"
                        ) AS suspended_ms
                        ',
                        
                        $from->format('Y-m-d H:i:s'),
                        $to->format('Y-m-d H:i:s'),
                        
                        $profileTable,
                        $to->format('Y-m-d H:i:s'),
                        $to->format('Y-m-d H:i:s'),
                        $to->format('Y-m-d H:i:s'),
                        $to->format('Y-m-d H:i:s'),
                        
                        $profileTable,
                        $to->format('Y-m-d H:i:s'),
                        $to->format('Y-m-d H:i:s'),
                        $to->format('Y-m-d H:i:s'),
                        $to->format('Y-m-d H:i:s'),
                        
                        $profileTable,
                        $to->format('Y-m-d H:i:s'),
                        $to->format('Y-m-d H:i:s'),
                        
                        $profileTable,
                        $to->format('Y-m-d H:i:s'),
                        $to->format('Y-m-d H:i:s'),
                        
                        $profileTable,
                        $to->format('Y-m-d H:i:s'),
                        $to->format('Y-m-d H:i:s'),
                        
                        $profileTable,
                        $to->format('Y-m-d H:i:s'),
                        $to->format('Y-m-d H:i:s')
                    )
                );
            }
            
            $this->_save($date->format('Y-m-d'), $data);
            $this->reportHelper->log(sprintf('Updated date %s', $date->format('Y-m-d')));
        } catch(\Exception $e) {
			$message = sprintf('Error during processing subscription_report_daily for date %s: %s', $date->format('Y-m-d'), $e->getMessage());
			
			$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
			
			$this->eventManager->dispatch(
				'toppikreport_system_add_message',
				['entity_type' => 'subscription_report_daily', 'entity_id' => null, 'message' => $message]
			);
        }
	}
	
    protected function _save($date, $data = array()) {
        if(count($data)) {
            $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            $values = array();
            
            foreach($data as $_data) {
                $values[] = sprintf(
                    '(%s, "%s", "%s", "%s", "%s", "%s", "%s")',
                    $connection->quote($date),
                    isset($_data['active']) ? $_data['active'] : 0,
                    isset($_data['active_ms']) ? $_data['active_ms'] : 0,
                    isset($_data['suspended']) ? $_data['suspended'] : 0,
                    isset($_data['suspended_ms']) ? $_data['suspended_ms'] : 0,
                    isset($_data['cancelled']) ? $_data['cancelled'] : 0,
                    isset($_data['cancelled_ms']) ? $_data['cancelled_ms'] : 0
                );
            }
            
            $connection->query(
                sprintf(
                    'DELETE FROM %s WHERE %s',
                    $this->resource->getTableName('subscription_report_daily'),
                    $connection->quoteInto('date = ?', $date)
                )
            );
            
            $connection->query(
                sprintf(
                    'INSERT INTO %s (
                        date,
                        active,
                        active_ms,
                        suspended,
                        suspended_ms,
                        cancelled,
                        cancelled_ms
                    ) VALUES %s',
                    $this->resource->getTableName('subscription_report_daily'),
                    implode(',', $values)
                )
            );
        }
    }
    
}
