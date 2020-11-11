<?php
namespace Toppik\Subscriptions\Controller\Customer;

class Frequency extends AbstractController {
    
    public function execute() {
		/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		
        try {
			$model = $this->_initProfile();
			
			if(!$model) {
				$this->messageManager->addError(__('This profile no longer exists.'));
				return $resultRedirect->setPath('*/*');
			}
			
			if($model->canEditFrequency() !== true) {
				$this->messageManager->addError(__('Can\'t edit frequency of current profile'));
				return $resultRedirect->setPath('*/*');
			}
			
			/** @var \Magento\Framework\View\Result\Page $resultPage */
			$resultPage = $this->resultPageFactory->create();
			
			/** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
			$navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
			
			if ($navigationBlock) {
				$navigationBlock->setActive('subscriptions/customer/index');
			}
			
			$block = $resultPage->getLayout()->getBlock('customer.account.link.back');
			
			if ($block) {
				$block->setRefererUrl($this->_redirect->getRefererUrl());
			}
			
			return $resultPage;
        } catch(\Exception $e) {
			$this->messageManager->addError($e->getMessage());
        }
		
		return $resultRedirect->setPath('*/*/view', array('id' => $model->getId()));
    }
	
}
