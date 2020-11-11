<?php
namespace Toppik\Subscriptions\Block\Customer\Account;

use Magento\Framework\ObjectManagerInterface;

class History extends \Magento\Framework\View\Element\Template
{
	
    /**
     * @var string
     */
    protected $_template = 'customer/account/history.phtml';
	
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;
	
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
	
    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    protected $profiles;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }
	
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Subscriptions'));
    }
	
    /**
     * @return bool|Toppik\Subscriptions\Model\ResourceModel\Profile\Collection
     */
    public function getCollection()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
		
        if (!$this->profiles) {
			$this->profiles = $this->objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
			
            $this->profiles
					->addFieldToFilter(
						'customer_id',
						$customerId
					)->setOrder(
						'created_at',
						'desc'
					);
        }
		
		return $this->profiles;
    }
	
    /**
     * @return bool
     */
    public function getAdminId()
    {
		return $this->_customerSession->getAdminId();
    }
	
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
		
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'customer.profiles.history.pager'
            )->setCollection(
                $this->getCollection()
            );
			
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
		
        return $this;
    }
	
    /**
     * @return string
     */
    public function getProfileName($profile) {
        $value = array();
        
        foreach($profile->getAllVisibleItems() as $_item) {
            $value[] = $_item->getName();
        }
        
        return implode(', ', $value);
    }
    
    /**
     * @return Phrase
     */
    public function getProfileStatus($profile) {
        $availableStatuses = $profile->getAvailableStatuses();
        $status = $profile->getStatus();
        
        if(isset($availableStatuses[$status])) {
            return $availableStatuses[$status];
        } else {
            return __('Unknown');
        }
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
	
    /**
     * @param object $profile
     * @return string
     */
    public function getViewUrl($profile)
    {
        return $this->getUrl('subscriptions/customer/view', ['id' => $profile->getId()]);
    }
	
    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
	
}
