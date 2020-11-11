<?php
namespace Toppik\Subscriptions\Block\Product;

class Add extends \Magento\Framework\View\Element\Template {

    const DEFAULT_IMAGE_ATTR = 'image';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $_productRepository;

    protected $_imageHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
		$this->registry = $registry;
        $this->_imageHelper = $imageHelper;
        $this->_productRepository = $productRepository;
    }

    /**
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->pageConfig->getTitle()->set($this->getTitle());
    }

    public function getTitle() {
		return __('Add Product');
    }

    public function getProduct() {
        return $this->registry->registry('product');
    }

    public function getAddModel() {
        return $this->registry->registry('subscriptions_add_model');
    }

    public function getProfile() {
        return $this->registry->registry('current_profile');
    }

    public function getImage($product, $attributeName = null) {
        if($attributeName && $product->getData($attributeName) && $product->getData($attributeName) != 'no_selection') {
            return $this->_imageHelper->init($product, $attributeName)->setImageFile($product->getData($attributeName));
        } else {
            return $this->_imageHelper->init($product, self::DEFAULT_IMAGE_ATTR)->setImageFile($product->getData(self::DEFAULT_IMAGE_ATTR));
        }
    }

    public function checkIsConfigurableProduct($productType) {
    	return $productType == 'configurable' ? true : false;
    }

    public function getPublicHashForProduct($productSku) {
    	return $this->_productRepository->get($productSku)->getData('subscription_product_add_hash') ?? '';
    }
}
