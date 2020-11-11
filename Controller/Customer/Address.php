<?php
namespace Toppik\Subscriptions\Controller\Customer;

class Address extends AbstractController {
    
    public function execute() {
		/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		
        try {
			$model = $this->_initProfile();
			
			if(!$model) {
				$this->messageManager->addError(__('This profile no longer exists.'));
				return $resultRedirect->setPath('*/*');
			}
			
			$type = trim(strtolower($this->getRequest()->getParam('type')));
			
            switch($type) {
                case 'billing':
                    if(!$model->canEditBillingAddress()) {
                        throw new \Exception('Can\'t edit billing address of current profile');
                    }
                    break;
                case 'shipping':
                    if(!$model->canEditShippingAddress()) {
                        throw new \Exception('Can\'t edit shipping address of current profile');
                    }
                    break;
                default:
                    throw new \Exception('Wrong params');
                    break;
            }
			
			/** @var \Magento\Framework\View\Result\Page $resultPage */
			$resultPage = $this->resultPageFactory->create();
			
			/** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
			$navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
			
			if($navigationBlock) {
				$navigationBlock->setActive('subscriptions/customer/index');
			}
			
			$block = $resultPage->getLayout()->getBlock('customer.account.link.back');
			
			if($block) {
				$block->setRefererUrl($this->_redirect->getRefererUrl());
			}
			
			return $resultPage;
        } catch(\Exception $e) {
			$this->messageManager->addError($e->getMessage());
        }
		
		return $resultRedirect->setPath('*/*/view', array('id' => $model->getId()));
    }
	
}
