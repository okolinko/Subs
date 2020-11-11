<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Points;

class Index extends \Toppik\Subscriptions\Controller\Adminhtml\Points\AbstractClass {
	
    public function execute() {
        $resultPage = $this->_initAction();
        return $resultPage;
    }
	
}
