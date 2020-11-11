<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Sku;

class Save extends \Toppik\Subscriptions\Controller\Adminhtml\Sku\AbstractClass {
	
    public function execute() {
		try {
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
            
            $data               = $this->getRequest()->getPost();
            
            $sku                = isset($data['sku']) ? $data['sku'] : null;
            $subscription_sku   = isset($data['subscription_sku']) ? $data['subscription_sku'] : null;
            $length             = isset($data['subscription_length']) ? $data['subscription_length'] : null;
            
            $model
                ->setData('sku', $sku)
                ->setData('subscription_sku', $subscription_sku)
                ->setData('subscription_length', $length)
                ->save();
		} catch(\Exception $e) {
			$this->messageManager->addError(__('An error has occurred: %1', $e->getMessage()));
		}
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();
        return $resultRedirect;
    }
    
}
