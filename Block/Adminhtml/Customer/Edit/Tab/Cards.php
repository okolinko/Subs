<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Customer\Edit\Tab;

class Cards extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Ui\Component\Layout\Tabs\TabInterface {
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    
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
    
    protected $_items;
    
    /**
     * @var \Magento\Braintree\Gateway\Config\Config
     */
    protected $config;
    
    /**
     * @var \Magento\Payment\Model\CcConfigProvider
     */
    private $_iconsProvider;
    
    /**
     * @var \Magento\Vault\Model\PaymentTokenManagement
     */
    private $paymentTokenManagement;
    
    /**
     * @var \Magento\Vault\Model\CustomerTokenManagement
     */
    private $customerTokenManagement;
    
    /**
     * @var \Magento\Braintree\Model\Ui\TokenUiComponentProvider
     */
    private $tokenUiComponentProvider;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
	
    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeAdapterFactory
     */
    private $_braintreeAdapterFactory;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Toppik\Subscriptions\Model\ResourceModel\Profile\Grid\CollectionFactory $collectionFactory,
        \Toppik\Subscriptions\Model\Profile $profileFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Vault\Model\CustomerTokenManagement $customerTokenManagement,
        \Magento\Braintree\Model\Ui\TokenUiComponentProvider $tokenUiComponentProvider,
        \Magento\Payment\Model\CcConfigProvider $iconsProvider,
        \Magento\Braintree\Gateway\Config\Config $config,
        \Magento\Vault\Model\PaymentTokenManagement $paymentTokenManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Braintree\Model\Adapter\BraintreeAdapterFactory $braintreeAdapterFactory,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->_coreRegistry = $coreRegistry;
        $this->customerRepository = $customerRepository;
        $this->_iconsProvider = $iconsProvider;
        $this->config = $config;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->customerTokenManagement = $customerTokenManagement;
        $this->tokenUiComponentProvider = $tokenUiComponentProvider;
        $this->storeManager = $storeManager;
        $this->_braintreeAdapterFactory = $braintreeAdapterFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    
    /**
     * Initialize customer edit tab profiles
     *
     * @return void
     */
    public function _construct() {
        parent::_construct();
        $this->setId('customer_edit_tab_stored_cards');
        $this->setUseAjax(true);
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
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel() {
        return __('Stored Cards');
    }
    
    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle() {
        return __('Stored Cards');
    }
    
    /**
     * Initialize child blocks
     *
     * @return $this
     */
    protected function _prepareLayout() {
        $customerId = null;
        
        if($this->getCustomerId()) {
            $customerId = $this->getCustomerId();
        } else if($this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID)) {
            $customerId = $this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
        }
        
        if($customerId) {
            $url = $this->getUrl('toppikvault/customer/card', ['customer_id' => $customerId]);
            
            $this->setChild(
                'add_card_button',
                $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(
                    [
                        'label' => __('New Card'),
                        'onclick' => "window.location.href = '{$url}'; return false;",
                        'class' => 'task'
                    ]
                )
            );
        }
        
        return parent::_prepareLayout();
    }
    
    /**
     * Generate list of grid buttons
     *
     * @return string
     */
    public function getMainButtonsHtml() {
        $html = '';
        
        if($this->getFilterVisibility()) {
            $html .= $this->getSearchButtonHtml();
            $html .= $this->getResetFilterButtonHtml();
        }
        
        $html .= $this->getAddCardButtonHtml();
        
        return $html;
    }
    
    /**
     * Generate search button
     *
     * @return string
     */
    public function getAddCardButtonHtml() {
        return $this->getChildHtml('add_card_button');
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
     * Prepare related item collection
     *
     * @return \Toppik\Subscriptions\Block\Adminhtml\Customer\Edit\Tab\Grid
     */
    protected function _prepareCollection() {
        if(!$this->getCollection()) {
            $collection = $this->objectManager->create('Magento\Framework\Data\Collection');
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
                $items = $this->paymentTokenManagement->getVisibleAvailableTokens($customerId);
                
                foreach($items as $_token) {
                    $details = \Zend\Json\Json::decode($_token->getDetails());
                    
                    $_token
                        ->setData('type', $details->type)
                        ->setData('number', str_repeat('*', 12) . $details->maskedCC)
                        ->setData('date', $details->expirationDate);
                    
                    $collection->addItem($_token);
                }
            }
            
            $this->setCollection($collection);
        }
        
        return parent::_prepareCollection();
    }
    
    /**
     * Prepare grid columns
     *
     * @return \Toppik\Subscriptions\Block\Adminhtml\Customer\Edit\Tab\Grid
     */
    protected function _prepareColumns() {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
                'type' => 'number'
            ]
        );
        
        $this->addColumn(
            'gateway_token',
            [
                'header' => __('Gateway Token'),
                'index' => 'gateway_token',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'number',
            [
                'header' => __('Card Number'),
                'index' => 'number',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'is_active',
            [
                'header' => __('Is Active'),
                'index' => 'is_active',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'date',
            [
                'header' => __('Expiration Date'),
                'index' => 'date',
                'type' => 'text'
            ]
        );
        
        return parent::_prepareColumns();
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
