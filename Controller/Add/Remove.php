<?php
namespace Toppik\Subscriptions\Controller\Add;

class Remove extends \Toppik\Subscriptions\Controller\Add\AbstractController {

    public function execute() {
		/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
        
        try {
			$id 		= (int) $this->getRequest()->getParam('id');
            $item_id    = (int) $this->getRequest()->getParam('item_id');
			$model 		= $this->_objectManager->create('Toppik\Subscriptions\Model\Profile')->load($id);
			$session 	= $this->_objectManager->get('Magento\Customer\Model\Session');
			
			if(!$model->getId()) {
				$this->messageManager->addError(__('This profile no longer exists.'));
				return $resultRedirect->setPath('subscriptions/customer');
			}
			
			if((int) $model->getCustomerId() !== (int) $session->getCustomerId()) {
				$this->messageManager->addError(__('This profile no longer exists.'));
				return $resultRedirect->setPath('subscriptions/customer');
			}
            
			if($model->canRemoveOneTimeProduct() !== true) {
				$this->messageManager->addError(__('Can\'t remove item'));
				return $resultRedirect->setPath('subscriptions/customer');
			}
            
            foreach($model->getAllVisibleItems() as $_item) {
                if((int) $_item->getIsOnetimeGift() === 1) {
                    if((int) $_item->getId() === $item_id) {
                        $_item->delete();
                        
                        if($_item->getHasChildren()) {
                            foreach($_item->getChildren() as $_child) {
                                $_child->delete();
                            }
                        }
                        
                        $model->updateProfile()->save();
                        $this->messageManager->addSuccess(__('"%1" has been removed successfully', $_item->getName()));
                        break;
                    }
                }
            }
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
        } catch(\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t remove item from your subscription right now'));
        }
        
        return $resultRedirect->setPath('subscriptions/customer/view', array('id' => $model->getId()));
    }
    
}
