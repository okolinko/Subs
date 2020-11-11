<?php
namespace Toppik\Subscriptions\Helper\Product;

class Composite extends \Magento\Catalog\Helper\Product\Composite {
    
    /**
     * Prepares and render result of composite product configuration request
     *
     * The $configureResult variable holds either:
     *  - 'ok' = true, and 'product_id', 'buy_request', 'current_store_id', 'current_customer_id'
     *  - 'error' = true, and 'message' to show
     *
     * @param \Magento\Framework\DataObject $configureResult
     * @return \Magento\Framework\View\Result\Layout
     */
    public function renderConfigureResult(\Magento\Framework\DataObject $configureResult) {
        try {
            if(!$configureResult->getOk()) {
                throw new \Magento\Framework\Exception\LocalizedException(__($configureResult->getMessage()));
            }
            
            $currentStoreId = $configureResult->hasStoreId() ? $configureResult->getStoreId() : $this->_storeManager->getStore()->getId();
            
            $product = $this->productRepository->getById($configureResult->getProductId(), false, $currentStoreId);
            
            $product->setCanSkipSubscriptionOption(true);
            
            $this->_coreRegistry->register('current_product', $product);
            $this->_coreRegistry->register('product', $product);
            
            // Prepare buy request values
            $buyRequest = $configureResult->getBuyRequest();
            
            if($buyRequest) {
                $this->_catalogProduct->prepareProductOptions($product, $buyRequest);
            }
            
            $isOk = true;
            $productType = $product->getTypeId();
        } catch(\Exception $e) {
            $isOk = false;
            $productType = null;
            $this->_coreRegistry->register('composite_configure_result_error_message', $e->getMessage());
        }
        
        return $this->_initConfigureResultLayout($isOk, $productType);
    }
    
}
