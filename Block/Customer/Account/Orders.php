<?php
namespace Toppik\Subscriptions\Block\Customer\Account;

use Magento\Framework\ObjectManagerInterface;

class Orders extends \Magento\Framework\View\Element\Template
{
	
    /**
     * @var string
     */
    protected $_template = 'customer/account/orders.phtml';
	
    /**
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
    */
    protected $_timezoneInterface;
	
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;
	
    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;
	
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;
	
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
	
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
	
    protected $_collection;
	
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Registry $registry,
        ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
		$this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderConfig = $orderConfig;
		$this->_timezoneInterface = $timezoneInterface;
        parent::__construct($context, $data);
    }
	
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set($this->getTitle());
    }
	
    public function getProfile()
    {
		return $this->registry->registry('current_profile');
    }
	
    public function getTitle()
    {
		$id = $this->getProfile()->getData('reference_id') ? $this->getProfile()->getData('reference_id') : $this->getProfile()->getId();
		return __('Subscription #%1', $id);
    }
	
    public function getCollection()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
		
        if (!$this->_collection) {
			$this->_collection = $this->_orderCollectionFactory->create();
			
			$this->_collection->join(
				'subscriptions_profiles_orders',
				'main_table.entity_id = subscriptions_profiles_orders.order_id'
			);
			
			$this->_collection->getSelect()
					->group('main_table.entity_id')
					->where('subscriptions_profiles_orders.profile_id = ?', $this->getProfile()->getId());
			
            $this->_collection
					->addFieldToFilter(
						'status',
						['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
					)->setOrder(
						'created_at',
						'desc'
					);
			
			$this->_collection->addAddressFields();
        }
		
		return $this->_collection;
    }
	
    public function getItemDate($item)
    {
		return $this->_timezoneInterface->date(new \DateTime($item->getCreatedAt()))->format('M d, Y');
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
	
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
	
    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }
	
}
