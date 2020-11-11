<?php
namespace Toppik\Subscriptions\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateDates extends Command {
    
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * @var ManagerInterface
     */
    private $eventManager;
	
    /**
     * @var ResourceConnection
     */
    private $resource;
	
    /**
     * @var State
     */
    private $state;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\State $state
    ) {
        $this->objectManager = $objectManager;
        $this->dateTime = $dateTime;
        $this->eventManager = $eventManager;
        $this->resource = $resource;
        $this->state = $state;
        parent::__construct();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('subscriptions:populatedates');
        $this->setDescription('Populate Dates');
        parent::configure();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->state->setAreaCode('frontend');
        
        $info   = array();
        $items  = $this->_getItems();
        
        $info[] = sprintf("Found %s item(s)\n", count($items));
        
        foreach($items as $_id) {
            try {
                $profile = $this->objectManager->get('Toppik\Subscriptions\Model\Profile');
                
                $profile->load($_id);
                
                if(!$profile->getId()) {
                    throw new \Exception(sprintf('Profile with ID %s does not exist', $_id));
                }
                
                if($profile->getStatus() == \Toppik\Subscriptions\Model\Profile::STATUS_SUSPENDED) {
                    $info[] = sprintf("Process suspended_at for profile ID %s", $profile->getId());
                    
                    $suspended_at   = null;
                    $date           = $this->_getLastOrderDate($profile->getId());
                    
                    $info[] = sprintf(" - Last order date for profile ID %s is %s", $profile->getId(), ($date ? $date->format('Y-m-d H:i:s') : ''));
                    
                    if($date) {
                        $date->add(new \DateInterval('P1D'));
                        $suspended_at = $date->format('Y-m-d H:i:s');
                    } else {
                        $_date = new \DateTime($profile->getCreatedAt());
                        $_date->add(new \DateInterval('P1D'));
                        $suspended_at = $_date->format('Y-m-d H:i:s');
                    }
                    
                    $profile->setSuspendedAt($suspended_at)->save();
                    
                    $info[] = sprintf(" - Saved suspended_at %s for profile ID %s\n", $suspended_at, $profile->getId());
                } else if($profile->getStatus() == \Toppik\Subscriptions\Model\Profile::STATUS_CANCELLED) {
                    $info[] = sprintf("Process cancelled_at for profile ID %s", $profile->getId());
                    
                    $cancelled_at   = null;
                    $date           = $this->_getLastOrderDate($profile->getId());
                    
                    $info[] = sprintf(" - Last order date for profile ID %s is %s", $profile->getId(), ($date ? $date->format('Y-m-d H:i:s') : ''));
                    
                    if($date) {
                        $date->add(new \DateInterval('P1D'));
                        $cancelled_at = $date->format('Y-m-d H:i:s');
                    } else {
                        $_date = new \DateTime($profile->getCreatedAt());
                        $_date->add(new \DateInterval('P1D'));
                        $cancelled_at = $_date->format('Y-m-d H:i:s');
                    }
                    
                    $profile->setCancelledAt($cancelled_at)->save();
                    
                    $info[] = sprintf(" - Saved cancelled_at %s for profile ID %s\n", $cancelled_at, $profile->getId());
                } else {
                    throw new \Exception(sprintf('Invalid status in profile ID %s and status %s', $profile->getId(), $profile->getStatus()));
                }
            } catch(\Exception $e) {
                $info[] = $e->getMessage();
            }
        }
        
        file_put_contents(BP . '/var/log/subscription-populatedate.log', implode("\n", $info), FILE_APPEND | LOCK_EX);
        $output->write( implode("\n", $info));
    }
    
	protected function _getItems() {
		$collection = array();
		
		$data = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION)->fetchAll(
			sprintf(
				'SELECT profile_id FROM %s
                WHERE (suspended_at IS NULL AND status = "suspended") || (cancelled_at IS NULL AND status = "cancelled")',
				$this->resource->getTableName('subscriptions_profiles')
			)
		);
		
		if(count($data)) {
			foreach($data as $_item) {
                if(isset($_item['profile_id'])) {
                    $collection[] = $_item['profile_id'];
                }
			}
		}
		
		return $collection;
	}
	
	protected function _getLastOrderDate($id) {
        $date = null;
        
        if($id && (int) $id > 0) {
            $data = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION)->fetchRow(
                sprintf(
                    'SELECT created_at FROM %s
                    WHERE entity_id IN (SELECT MAX(order_id) FROM %s WHERE profile_id = %s GROUP BY profile_id)',
                    $this->resource->getTableName('sales_order'),
                    $this->resource->getTableName('subscriptions_profiles_orders'),
                    (int) $id
                )
            );
            
            if(isset($data['created_at']) && !empty($data['created_at'])) {
                $date = new \DateTime($data['created_at']);
            }
        }
        
        return $date;
	}
	
}
