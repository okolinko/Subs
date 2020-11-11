<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Add;

class Edit extends \Toppik\Subscriptions\Controller\Adminhtml\Add\AbstractClass {
	
    public function execute() {
        $id = (int) $this->getRequest()->getParam('id');
        
        $model = $this->_objectManager->create('\Toppik\Subscriptions\Model\Profile\Add');
        
        if($id) {
            $model->load($id);
            
            if(!$model->getId()) {
                $this->messageManager->addError(__('The item ID %1 no longer exists.', $id));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        
        $this->_coreRegistry->register('add_item', $model);
        
        $resultPage = $this->_initAction();
        
        $resultPage->getConfig()->getTitle()->prepend(($model->getId() ? __('Edit item ID %1', $model->getId()) : __('Add New Item')));
        
        return $resultPage;
    }
    
}
