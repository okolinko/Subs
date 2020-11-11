<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Sku;

class Edit extends \Toppik\Subscriptions\Controller\Adminhtml\Sku\AbstractClass {
	
    public function execute() {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = $this->_objectManager->create('\Toppik\Subscriptions\Model\Sku');
        
        if($id) {
            $model->load($id);
            
            if(!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        
        $this->_coreRegistry->register('item', $model);
        
        $resultPage = $this->_initAction();
        
        $resultPage->addBreadcrumb(
            $model->getId() ? __('Edit item # %1', $model->getId()) : __('Add New Item'),
            $model->getId() ? __('Edit item # %1', $model->getId()) : __('Add New Item')
        );
        
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit item # %1', $model->getId()) : __('Add New Item'));
        
        return $resultPage;
    }
    
}
