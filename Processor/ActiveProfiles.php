<?php
namespace Toppik\Subscriptions\Processor;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Toppik\Subscriptions\Converter\ProfileToOrder;
use Toppik\Subscriptions\Helper\Profile as ProfileHelper;
use Toppik\Subscriptions\Model\Profile;
use Toppik\Subscriptions\Model\ResourceModel\Profile as ProfileResourceModel;
use Toppik\Subscriptions\Model\ResourceModel\Profile\Collection;
use Magento\Framework\Stdlib\DateTime\DateTime;

class ActiveProfiles {

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var DateTime
     */
    private $dateTime;
    /**
     * @var ProfileHelper
     */
    private $profileHelper;
    /**
     * @var ProfileToOrder
     */
    private $profileToOrder;
    /**
     * @var ProfileResourceModel
     */
    private $profileResourceModel;

    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;
	
    /**
     * @var ManagerInterface
     */
    private $eventManager;
	
    /**
     * ActiveProfiles constructor.
     * @param ObjectManagerInterface $objectManager
     * @param DateTime $dateTime
     * @param ProfileHelper $profileHelper
     * @param ProfileToOrder $profileToOrder
     * @param ProfileResourceModel $profileResourceModel
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DateTime $dateTime,
        ProfileHelper $profileHelper,
        ProfileToOrder $profileToOrder,
        ProfileResourceModel $profileResourceModel,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->objectManager = $objectManager;
        $this->dateTime = $dateTime;
        $this->profileHelper = $profileHelper;
        $this->profileToOrder = $profileToOrder;
        $this->profileResourceModel = $profileResourceModel;
		$this->reportHelper = $reportHelper;
        $this->eventManager = $eventManager;
    }
	
    public function execute() {
		try {
			$this->_prepare();
			$this->_process();
		} catch(\Exception $e) {
			$message = sprintf('ActiveProfiles: %s', $e->getMessage());
			
			$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
			
			$this->eventManager->dispatch(
				'toppikreport_system_add_message',
				[
					'entity_type' 	=> 'subscription_active_profile',
					'entity_id' 	=> null,
					'message' 		=> $message
				]
			);
		}
    }
	
	protected function _prepare() {
        /* @var Collection $profileCollection */
        $profileCollection = $this->objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
		
        $profileCollection
            ->addFieldToFilter(Profile::STATUS, array('in' => array(Profile::STATUS_ACTIVE, Profile::STATUS_SUSPENDED_TEMPORARILY)))
            ->addFieldToFilter(Profile::START_DATE, ['lteq' => $this->dateTime->gmtDate('Y-m-d')])
            ->addFieldToFilter(Profile::NEXT_ORDER_AT, [
                ['null' => true],
                ['lteq' => $this->dateTime->gmtDate('Y-m-d H:i:s')]
            ]);
		
        foreach($profileCollection->getItems() as $profile) {
            try {
				$this->profileToOrder->validateProfile($profile);
            } catch(\Exception $e) {
				$message = sprintf('Profile Validate -> Suspending profile ID %s: %s', $profile->getId(), $e->getMessage());
				
                $profile->changeStatusToSuspend($message, $e->getCode());
                
				$this->reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
				
				$this->_logProfile($profile, $message);
                
				try {
					$this->reportHelper->send(array($this->_createDataByProfile($profile, $message)), $message);
					$this->reportHelper->sendSuspendNotifications($profile, $message);
				} catch(\Exception $e) {
					$this->reportHelper->log(
						sprintf('%s CANNOT send report: %s', str_repeat('=', 5), $e->getMessage()),
						[],
						\Toppik\Subscriptions\Logger\Logger::ERROR
					);
				}
            }
        }
	}
	
	protected function _process() {
		$this->reportHelper->log("ActiveProfiles - > start", []);
		
        /* @var Collection $profileCollection */
        $profileCollection = $this->objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
        $profileCollection
            ->addFieldToFilter(Profile::STATUS, array('in' => array(Profile::STATUS_ACTIVE, Profile::STATUS_SUSPENDED_TEMPORARILY)))
            ->addFieldToFilter(Profile::START_DATE, ['lteq' => $this->dateTime->gmtDate('Y-m-d')])
            ->addFieldToFilter(Profile::NEXT_ORDER_AT, [
                ['null' => true, ],
                ['lteq' => $this->dateTime->gmtDate('Y-m-d H:i:s'), ]
            ]);
		
		$this->reportHelper->log(sprintf('%s Found %s profile(s)', str_repeat('-', 5), count($profileCollection->getItems())), []);
		
        $processedProfileIds = [];
		
        foreach($profileCollection->getItems() as $profile) {
            /* @var Profile $profile */
            if(in_array($profile->getId(), $processedProfileIds)) {
				$this->reportHelper->log(sprintf('Skipping profile ID %s', $profile->getId()), []);
                continue;
            }
			
			$this->reportHelper->log(sprintf(' %s> Start processing profile ID %s <%s ', str_repeat('-', 10), $profile->getId(), str_repeat('-', 10)), []);
			
            $similarProfiles = $this->profileHelper->findSimilarProfiles($profile, $profileCollection->getItems());
			
			$this->reportHelper->log(sprintf('Found %s similiar profiles', count($similarProfiles)), []);
			
            try {
                $processedProfileIds[] = $profile->getId();
                
                foreach($similarProfiles as $similarProfile) {
                    $processedProfileIds[] = $similarProfile->getId();
                }
                
                $order = $this->profileToOrder->process($profile, $similarProfiles);
                
				$this->reportHelper->log(sprintf('Created new order ID %s and increment ID %s', $order->getId(), $order->getIncrementId()), []);
				
                // Update profiles data
                $profile->setLastOrderId($order->getId());
                $profile->setLastOrderAt($order->getCreatedAt());
                $profile->scheduleNextOrder();
                $profile->setLastSuspendError('');
                $profile->save();
				
				$this->reportHelper->log(sprintf('Next order date for profile ID %s: %s', $profile->getId(), $profile->getNextOrderAt()), []);
				
                foreach($similarProfiles as $similarProfile) {
                    $similarProfile->setLastOrderId($order->getId());
                    $similarProfile->setLastOrderAt($order->getCreatedAt());
                    $similarProfile->scheduleNextOrder();
                    $similarProfile->setLastSuspendError('');
                    $similarProfile->save();
					
					$this->reportHelper->log(
						sprintf('Next order date for similiar profile ID %s: %s', $similarProfile->getId(), $similarProfile->getNextOrderAt()),
						[]
					);
                }
                
                $this->_removeGifts(array_merge(array($profile), $similarProfiles));
            } catch(\Exception $e) {
                $profiles = array_merge(array($profile), $similarProfiles);
                
                foreach($profiles as $_profile) {
                    $message = sprintf('Error during processing profile ID %s: %s', $_profile->getId(), $e->getMessage());
                    
                    if($this->_isTransactionError($message) === true) {
                        $_profile->setErrorCode(\Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_PAYMENT_TRANSACTION);
                        $_profile->setLastSuspendError($message);
                        
                        if($_profile->getStatus() == Profile::STATUS_SUSPENDED_TEMPORARILY) {
                            $_profile->setStatusHistoryCode('retry');
                            $_profile->setStatusHistoryMessage(__('Retry # %1 has failed', $_profile->getSuspendCounter()));
                        }
                        
                        $_profile->scheduleRetry();
                    } else {
                        $_profile->changeStatusToSuspend($message, $e->getCode());
                    }
                    
                    $this->reportHelper->log(sprintf('%s ActiveProfiles -> %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
                    
                    $this->_logProfile($_profile, $message);
                    
                    try {
                        $this->reportHelper->send(array($this->_createDataByProfile($_profile, $message)), $message);
                        $this->reportHelper->sendSuspendNotifications($_profile, $message);
                    } catch(\Exception $e) {
                        $this->reportHelper->log(
                            sprintf('%s CANNOT send report: %s', str_repeat('=', 5), $e->getMessage()),
                            [],
                            \Toppik\Subscriptions\Logger\Logger::ERROR
                        );
                    }
                }
            }
        }
	}
	
    protected function _removeGifts($profiles) {
        foreach($profiles as $_profile) {
            if(count($_profile->getAllGiftItems()) > 0) {
                $messages = array();
                
                foreach($_profile->getAllGiftItems() as $_item) {
                    $messages[] = sprintf(
                        'Remove gift item in profile ID %s: item ID %s, product_type %s, sku %s, price %s',
                        $_profile->getId(),
                        $_item->getId(),
                        $_item->getProductType(),
                        $_item->getSku(),
                        $_item->getPrice()
                    );
                    
                    $_item->delete();
                }
                
                $this->reportHelper->log(implode('. ', $messages), []);
                
                $_profile->setStatusHistoryCode('remove_gift');
                $_profile->setStatusHistoryMessage(implode('. ', $messages));
                $_profile->save();
            }
        }
    }
    
    protected function _isTransactionError($message) {
        $value = false;
        
        if(strpos($message, 'transaction') !== false) {
            $value = true;
        } else if(strpos($message, 'decline') !== false) {
            $value = true;
        }
        
        return $value;
    }
    
	protected function _logProfile($profile, $message) {
		$billing = $profile->getBillingAddress();
		
		$this->eventManager->dispatch(
			'toppikreport_system_add_message',
			[
				'entity_type' 		=> 'subscription_active_profile',
				'entity_id' 		=> $profile->getId(),
				'message' 			=> $message,
				'amount' 			=> $profile->getGrandTotal(),
				'customer_id' 		=> $profile->getCustomerId(),
				'customer_name' 	=> sprintf('%s %s', $billing->getData('firstname'), $billing->getData('lastname')),
				'customer_email' 	=> $billing->getData('email'),
				'customer_phone' 	=> $billing->getData('telephone')
			]
		);	
	}
	
	protected function _createDataByProfile($profile, $message) {
		return array(
			'Subscription ID' 	=> $profile->getId(),
			'Next Order Date' 	=> $profile->getNextOrderAt(),
			'First Name' 		=> $profile->getBillingAddress()->getData('firstname'),
			'Last Name' 		=> $profile->getBillingAddress()->getData('lastname'),
			'Email' 			=> $profile->getBillingAddress()->getData('email'),
			'Phone' 			=> $profile->getBillingAddress()->getData('telephone'),
			'Total' 			=> $profile->getGrandTotal(),
			'Error' 			=> $message
		);
	}
	
}
