<?php
namespace Toppik\Subscriptions\Controller\Ajax\Profile;

class Customer extends \Magento\Framework\App\Action\Action {
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;
    
    /**
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
    */
    protected $_timezoneInterface;
	
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
	
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;
	
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    ) {
        $this->dateTime = $dateTime;
		$this->_timezoneInterface = $timezoneInterface;
        $this->jsonFactory = $jsonFactory;
        $this->_customerSession = $customerSession;
        $this->_priceHelper = $priceHelper;
        parent::__construct($context);
    }
    
    /**
     * Order view page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        $data = array();
        $json = $this->jsonFactory->create();
        
        try {
            if(!$this->_customerSession->isLoggedIn() || !$this->_customerSession->getCustomerId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please login to your account to continue!'));
            }
            
			$profiles = $this->_objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
			
            $profiles
					->addFieldToFilter(
                        \Toppik\Subscriptions\Model\Profile::CUSTOMER_ID,
						$this->_customerSession->getCustomerId()
					)
                    ->addFieldToFilter(
                        \Toppik\Subscriptions\Model\Profile::STATUS,
                        array(
                            'in' => array(
                                \Toppik\Subscriptions\Model\Profile::STATUS_ACTIVE,
                                \Toppik\Subscriptions\Model\Profile::STATUS_SUSPENDED_TEMPORARILY
                            )
                        )
                    )
                    /* ->addFieldToFilter(
                        \Toppik\Subscriptions\Model\Profile::NEXT_ORDER_AT,
                        [
                            ['gteq' => $this->dateTime->gmtDate('Y-m-d H:i:s')]
                        ]
                    ) */
                    ->setOrder(
                        \Toppik\Subscriptions\Model\Profile::CREATED_AT,
						'desc'
					);
            
            if(count($profiles->getItems()) < 1) {
                throw new \Magento\Framework\Exception\LocalizedException(__('You do not have active profiles!'));
            }
            
            $items = array();
            
            foreach($profiles as $_profile) {
                $items[$_profile->getId()] = array(
                    'profile_id'    => $_profile->getId(),
                    'next_order_at' => $this->_timezoneInterface->date(new \DateTime($_profile->getNextOrderAt()))->format('M d, Y'),
                    'total'         => $this->_priceHelper->currency($_profile->getGrandTotal(), true, false),
                    'frequency'     => $_profile->getFrequencyTitle(),
                    'sku'           => $_profile->getSku()
                );
            }
            
            $data['success'] = true;
            $data['data'] = $items;
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $data['success'] = false;
            $data['message'] = $e->getMessage();
        } catch(\Exception $e) {
            $data['success'] = false;
            $data['message'] = __('Something went wrong while loading data.');
        }
        
        $json->setData($data);
        
        return $json;
    }
    
}
