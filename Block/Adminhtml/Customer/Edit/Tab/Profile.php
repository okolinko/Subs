<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Customer\Edit\Tab;

class Profile extends \Toppik\Subscriptions\Block\Adminhtml\Profile\Grid implements \Magento\Ui\Component\Layout\Tabs\TabInterface {
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Toppik\Subscriptions\Model\ResourceModel\Profile\Grid\CollectionFactory $collectionFactory
     * @param \Toppik\Subscriptions\Model\Profile $profileFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Toppik\Subscriptions\Model\ResourceModel\Profile\Grid\CollectionFactory $collectionFactory,
        \Toppik\Subscriptions\Model\Profile $profileFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $backendHelper, $collectionFactory, $profileFactory, $data);
    }
    
    /**
     * Initialize customer edit tab profiles
     *
     * @return void
     */
    public function _construct() {
        parent::_construct();
        $this->setId('customer_edit_tab_subscription_profiles');
        $this->setUseAjax(true);
    }
    
    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel() {
        return __('Recurring Profiles');
    }
    
    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle() {
        return __('Recurring Profiles');
    }
    
    /**
     * Prepare massaction
     *
     * @return $this
     */
    protected function _prepareMassaction() {
        return $this;
    }
    
    /**
     * Configuring and setting collection
     *
     * @return $this
     */
    protected function _beforePrepareCollection() {
        $customerId = null;
        
		if($this->getCustomerId()) {
            $customerId = $this->getCustomerId();
        } else if($this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID)) {
			$customer = $this->customerRepository->getById(
				$this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID)
			);
			
			if($customer && $customer->getId()) {
				$customerId = $customer->getId();
			}
        }
		
        if($customerId) {
            $collection = $this->_collectionFactory->create()->addFieldToFilter('main_table.customer_id', $customerId);
            $this->setCollection($collection);
        }
		
        return $this;
    }
    
    /**
     * Prepare grid columns
     *
     * @return \Toppik\Subscriptions\Block\Adminhtml\Customer\Edit\Tab\Grid
     */
    protected function _prepareColumns() {
        parent::_prepareColumns();
    }
    
    /**
     * Get Url to action to reload grid
     *
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('subscriptions/profiles/profileGrid', ['_current' => true]);
    }
    
    /**
     * Check if can show tab
     *
     * @return boolean
     */
    public function canShowTab() {
        $customerId = $this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
        return (bool)$customerId;
    }
    
    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden() {
        return false;
    }
    
    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass() {
        return '';
    }
    
    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl() {
        return '';
    }
    
    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded() {
        return false;
    }
    
}
