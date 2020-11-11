<?php
namespace Toppik\Subscriptions\Plugin\Magento\Checkout\CustomerData;

class Cart {
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Data
     */
    protected $_subscriptionHelper;
	
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;
    
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    
    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;
    
    /**
     * @param \Toppik\Subscriptions\Helper\Data $subscriptionHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
		\Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
        $this->checkoutSession = $checkoutSession;
		$this->_subscriptionHelper = $subscriptionHelper;
        $this->_productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->checkoutHelper = $checkoutHelper;
    }
    
    /**
     * Add link to cart in cart sidebar to view grid with failed products
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(
        \Magento\Checkout\CustomerData\Cart $subject,
        $result
    ) {
        try {
            $items = isset($result['items']) ? $result['items'] : array();
            
            if($items && count($items) > 0) {
                foreach($items as $_item_key => $_item_data) {
                    try {
                        $found = false;
                        
                        foreach(array_reverse($this->getQuote()->getAllVisibleItems()) as $_quote_item) {
                            if($found === true) {
                                break;
                            }
                            
                            if(isset($_item_data['item_id']) && (int) $_item_data['item_id'] === (int) $_quote_item->getId()) {
                                $upsell = $_quote_item->getProduct()->getUpSellProducts();
                                
                                if($upsell && count($upsell) > 0) {
                                    foreach($upsell as $_product) {
                                        if($this->_subscriptionHelper->productHasSubscription($_product)) {
                                            $subscription = $this->_subscriptionHelper->getSubscriptionByProduct($_product);
                                            
                                            if($subscription) {
                                                foreach($subscription->getItemsCollection() as $_item) {
                                                    $found = true;
                                                    $product = $this->_productRepository->getById($_product->getId());
                                                    $imageHelper = $this->imageHelper->init($product, 'mini_cart_product_thumbnail');
                                                    
                                                    $result['items'][$_item_key] = array_merge(
                                                        $result['items'][$_item_key],
                                                        array(
                                                            'subscription_item_product_url' => $product->getProductUrl(),
                                                            'subscription_item_has_product_url' => true,
                                                            'subscription_item_image' => [
                                                                'src' => $imageHelper->getUrl(),
                                                                'alt' => $imageHelper->getLabel(),
                                                                'width' => $imageHelper->getWidth(),
                                                                'height' => $imageHelper->getHeight(),
                                                            ],
                                                            'subscription_item_product_name' => $product->getName(),
                                                            'subscription_item_product_price' => $this->checkoutHelper->formatPrice($product->getFinalPrice()),
                                                            'subscription_item_subscription_price' => $this->checkoutHelper->formatPrice($_item->getRegularPrice())
                                                        )
                                                    );
                                                    
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } catch(\Exception $e) {
                        
                    }
                }
            }
        } catch(\Exception $e) {
            
        }
        
        return $result;
    }
    
    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote() {
        if(null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        
        return $this->quote;
    }
    
}
