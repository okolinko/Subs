<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Sku;

class Delete extends \Toppik\Subscriptions\Controller\Adminhtml\Sku\AbstractClass {
	
    public function execute() {
        $id = (int) $this->getRequest()->getParam('id');
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if($id) {
            try {
                $model = $this->_objectManager->create('Toppik\Subscriptions\Model\Sku');
                
                $model->load($id);
                
                if(!$model->getId()) {
                    $this->messageManager->addError(__('This item no longer exists.'));
                    /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/');
                }
                
                $model->delete();
                $this->messageManager->addSuccess(__('The item has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch(\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            } catch(\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while deleting the item.'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        
        $this->messageManager->addError(__('We can\'t find item to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
    
}
