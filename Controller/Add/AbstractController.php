<?php
namespace Toppik\Subscriptions\Controller\Add;

abstract class AbstractController extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /** @var \Magento\Framework\Escaper */
    protected $escaper;

    protected $_storeManager;
    protected $_coreRegistry;
    
    /**
     * @var Data
     */
    protected $_subscriptionHelper;
	
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Escaper $escaper,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Registry $coreRegistry,
		\Toppik\Subscriptions\Helper\Data $subscriptionHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
		$this->registry = $registry;
        $this->productHelper = $productHelper;
        $this->productRepository = $productRepository;
        $this->escaper = $escaper;
        $this->_storeManager = $storeManager;
		$this->_coreRegistry = $coreRegistry;
		$this->_subscriptionHelper = $subscriptionHelper;
    }

    protected function _getProfile() {
        $collection = $this->_objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');

        $collection
            ->addFieldToFilter(
                \Toppik\Subscriptions\Model\Profile::CUSTOMER_ID,
                $this->_customerSession->getCustomerId()
            )
            ->addFieldToFilter(
                \Toppik\Subscriptions\Model\Profile::STATUS,
                array(
                    'in' => array(
                        \Toppik\Subscriptions\Model\Profile::STATUS_ACTIVE
                    )
                )
            )
            ->setOrder(
                \Toppik\Subscriptions\Model\Profile::NEXT_ORDER_AT,
                'asc'
            );

        if(count($collection->getItems()) < 1) {
            throw new \Magento\Framework\Exception\LocalizedException(__('You do not have active profiles!'));
        }

        $model = $collection->getFirstItem();

        if(!$model || !$model->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('You do not have active profiles!'));
        }

        return $model;
    }

    protected function _getAddItem($public_hash = null) {
        if(!$public_hash) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unknown rule provided'));
        }

        $collection = $this->_objectManager->get('\Toppik\Subscriptions\Model\ResourceModel\Profile\Add\CollectionFactory')->create();

        $collection
            ->addFieldToFilter('public_hash', $public_hash)
            ->addFieldToFilter('status', \Toppik\Subscriptions\Model\Profile\Add::STATUS_ENABLED);

        if(count($collection->getItems()) !== 1) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Rule "%1" does not exist', $public_hash));
        }

        $model = $collection->getFirstItem();

        if(!$model || !$model->getSku()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Rule "%1" does not exist', $public_hash));
        }

        return $model;
    }

    protected function _getProductBySku($sku) {
        try {
            $product = $this->productRepository->get($sku);
        } catch(\Exception $e) {
            throw new \Exception(sprintf('%s: product # "%s"', $e->getMessage(), $sku));
        }

        return $product;
    }

    protected function _hasProduct($model, $sku) {
        $has_product = false;

        if($sku) {
            foreach($model->getAllItems() as $_item) {
                if($_item->getSku() == $sku) {
                    $has_product = true;
                    break;
                }
            }
        }

        return $has_product;
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _buildUrl($route = '', $params = []) {
        /** @var \Magento\Framework\UrlInterface $urlBuilder */
        $urlBuilder = $this->_objectManager->create('Magento\Framework\UrlInterface');
        return $urlBuilder->getUrl($route, $params);
    }

}
