<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Sku;

class Index extends \Toppik\Subscriptions\Controller\Adminhtml\Sku\AbstractClass {
	
    public function execute() {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Sku Relations'));
        return $resultPage;
    }
	
}
