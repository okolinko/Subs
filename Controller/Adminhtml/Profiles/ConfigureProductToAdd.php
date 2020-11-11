<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;

class ConfigureProductToAdd extends \Toppik\Subscriptions\Controller\Adminhtml\Points\AbstractClass {
	
    public function execute() {
        $productId = (int) $this->getRequest()->getParam('id');
        $store_id = $this->getRequest()->getParam('store_id') ? (int) $this->getRequest()->getParam('store_id') : null;
        
        $configureResult = new \Magento\Framework\DataObject();
        $configureResult->setOk(true);
        $configureResult->setProductId($productId);
        $configureResult->setStoreId($store_id);
        
        $helper = $this->_objectManager->get('Toppik\Subscriptions\Helper\Product\Composite');
        return $helper->renderConfigureResult($configureResult);
    }
    
    /**
     * @return bool
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_management');
    }
	
}
