<?php
namespace Toppik\Subscriptions\Block\Customer\Account\Edit;

class Product extends \Magento\Framework\View\Element\Template {
    
    /**
     * @var string
     */
    protected $_template = 'customer/account/edit/product.phtml';
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var Data
     */
    protected $_subscriptionHelper;
	
    protected $_productRepository;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
		$this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_storeManager = $storeManager;
		$this->_subscriptionHelper = $subscriptionHelper;
        $this->_productRepository = $productRepository;
        parent::__construct($context, $data);
    }
    
    /**
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->pageConfig->getTitle()->set($this->getTitle());
    }
    
    public function getProfile() {
		return $this->registry->registry('current_profile');
    }
    
    public function getTitle() {
		return __('Edit Subscription # %1', $this->getProfile()->getId());
    }
    
    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection() {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionFactory->create();
        $collection
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->_storeManager->getStore())
            ->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->addAttributeToFilter(\Toppik\Subscriptions\Helper\Data::ATTRIBUTE_USE, true)
            ->addAttributeToFilter(
                'status',
                ['eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED]
            )
            ->setOrder('name', 'ASC');
        $collection->getSelect()->limit(100);
        
        if($this->_subscriptionHelper->getIsChangeModeFull() !== true) {
            $group_id = 0;
            
            foreach($this->getProfile()->getAllVisibleItems() as $_item) {
                if((int) $_item->getData('is_onetime_gift') !== 1) {
                    $group_id = (int) $this->_productRepository->getById($_item->getProductId())->getData(\Toppik\Subscriptions\Helper\Data::ATTRIBUTE_GROUP);
                    break;
                }
            }
            
            $collection->addAttributeToFilter(\Toppik\Subscriptions\Helper\Data::ATTRIBUTE_GROUP, $group_id);
        }
        
        $values = array();
        
        foreach($collection as $_product) {
            $group_id = $_product->getData(\Toppik\Subscriptions\Helper\Data::ATTRIBUTE_GROUP) ? $_product->getData(\Toppik\Subscriptions\Helper\Data::ATTRIBUTE_GROUP) : 0;
            
            if(!isset($values[$group_id])) {
                $values[$group_id] = array();
            }
            
            $values[$group_id][] = $_product;
        }
        
        return $values;
    }
    
}
