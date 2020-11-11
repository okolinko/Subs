<?php
namespace Toppik\Subscriptions\Observer;

class CatalogProductInitSubscriptionOptions implements \Magento\Framework\Event\ObserverInterface {
    
    /**
     * @var \Toppik\Subscriptions\Helper\Data
     */
    private $susbcriptionHelper;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Product
     */
    private $productHelper;
    
    /**
     * CatalogControllerProductView constructor.
     * @param \Toppik\Subscriptions\Helper\Data $susbcriptionHelper
     * @param \Toppik\Subscriptions\Helper\Product $productHelper
     */
    public function __construct(
        \Toppik\Subscriptions\Helper\Data $susbcriptionHelper,
        \Toppik\Subscriptions\Helper\Product $productHelper
    ) {
        $this->susbcriptionHelper = $susbcriptionHelper;
        $this->productHelper = $productHelper;
    }
    
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /* @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getProduct();
        
        if($this->susbcriptionHelper->productHasSubscription($product)) {
            $option = $this->productHelper->getSubscriptionTypeProductOption($product, __(\Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_CART_LABEL));
            $product->addOption($option);
            
            $preconfiguredValues = $product->getPreconfiguredValues();
            $product->setData('preconfigured_values', $preconfiguredValues);
            $options = $preconfiguredValues->getData('options');
            
            if(!is_array($options)) {
                $options = [];
            }
            
            $options[$option->getId()] = \Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_OPTION_EMPTY_VALUE;
            $preconfiguredValues->setData('options', $options);
            
            $product->setHasOptions(true);
            $product->setRequiredOptions(true);
        }
    }
    
}
