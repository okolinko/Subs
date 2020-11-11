<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;

class ProductChooserGrid extends \Magento\Backend\App\Action {
    
    public function execute() {
        $store_id = $this->getRequest()->getParam('store_id') ? (int) $this->getRequest()->getParam('store_id') : null;
        
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Toppik\Subscriptions\Block\Adminhtml\Profile\Edit\Product\Grid'
            )
            ->setStoreId($store_id)
            ->toHtml()
        );
    }
	
}
