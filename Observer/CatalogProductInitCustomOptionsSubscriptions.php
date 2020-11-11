<?php
namespace Toppik\Subscriptions\Observer;

class CatalogProductInitCustomOptionsSubscriptions implements \Magento\Framework\Event\ObserverInterface {
    
    private $request;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Data
     */
    private $susbcriptionHelper;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Product
     */
    private $productHelper;
    
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Toppik\Subscriptions\Helper\Data $susbcriptionHelper,
        \Toppik\Subscriptions\Helper\Product $productHelper
    ) {
        $this->request = $request;
        $this->susbcriptionHelper = $susbcriptionHelper;
        $this->productHelper = $productHelper;
    }
    
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $ids        = array();
        $product    = $observer->getProduct();
        
        if($product->getSubscriptionsCustomOptionsGenerated() === true) {
            return $this;
        }
        
        if($product->getCanSkipSubscriptionOption() === true) {
            return $this;
        }
        
        if(is_array($product->getData('options'))) {
            foreach($product->getData('options') as $_option) {
                $ids[] = $_option->getId();
            }
            
            if(count($ids) >= 1) {
                if(
                    in_array(\Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_OPTION_ID, $ids)
                ) {
                    $product->setSubscriptionsCustomOptionsGenerated(true);
                    return $this;
                }
            }
        }
        
        if($this->request->getModuleName() == 'catalog') {
            if($this->request->getControllerName() == 'product') {
                if($this->request->getActionName() == 'edit' || $this->request->getActionName() == 'save') {
                    $product->setCanSkipSubscriptionOption(true);
                    return $this;
                }
            }
        }
        
        if($this->request->getModuleName() == 'subscriptions') {
            if($this->request->getControllerName() == 'points') {
                $product->setCanSkipSubscriptionOption(true);
                return $this;
            }
        }
        
        if($this->susbcriptionHelper->productHasSubscription($product)) {
            $preconfiguredValues = $product->getPreconfiguredValues();
            $product->setData('preconfigured_values', $preconfiguredValues);
            $options = $preconfiguredValues->getData('options');
            
            if(!is_array($options)) {
                $options = [];
            }
            
            if(!in_array(\Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_OPTION_ID, $ids)) {
                $optionPeriod = $this->productHelper->getSubscriptionTypeProductOption(
                    $product,
                    __(\Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_CART_LABEL)
                );
                
                $product->addOption($optionPeriod);
                
                $options[$optionPeriod->getId()] = \Toppik\Subscriptions\Model\Preferences::SUBSCRIPTION_OPTION_EMPTY_VALUE;
            }
            
            $preconfiguredValues->setData('options', $options);
            
            $product->setHasOptions(true);
            $product->setRequiredOptions(true);
            
            $product->setSubscriptionsCustomOptionsGenerated(true);
        }
    }
    
}
