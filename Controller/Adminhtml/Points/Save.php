<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Points;

class Save extends \Toppik\Subscriptions\Controller\Adminhtml\Points\AbstractClass {
	
    public function execute() {
		try {
            $data       = $this->getRequest()->getPost();
            $id         = isset($data['id']) ? (int) $data['id'] : null;
            $model      = $this->_objectManager->create('\Toppik\Subscriptions\Model\Profile\Points');
            
            if($id) {
                $model->load($id);
                
                if(!$model->getId()) {
                    throw new \Exception(__('The item ID %1 no longer exists.', $id));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/');
                }
            }
            
            $type_id = isset($data['type_id']) ? (int) $data['type_id'] : 0;
            $rule_id = isset($data['rule_id']) ? (int) $data['rule_id'] : 0;
            
            if(!in_array($type_id, array_keys($model->getAvailableTypes()))) {
                throw new \Exception(__('Unknown type ID: %1', $type_id));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            
            if($type_id === \Toppik\Subscriptions\Model\Profile\Points::TYPE_COUPON) {
                if(!$rule_id || $rule_id < 1) {
                    throw new \Exception(__('Please specify rule ID'));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/');
                }
                
                $rule = $this->_ruleFactory->create()->load($rule_id);
                
                if(!$rule->getId()) {
                    throw new \Exception(__('Rule ID %1 does not exist', $rule_id));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/');
                }
            }
            
            $model
                ->setData('type_id', $type_id)
                ->setData('rule_id', $rule_id)
                ->setData('title', (isset($data['title']) ? strip_tags($data['title']) : null))
                ->setData('description', (isset($data['description']) ? strip_tags($data['description']) : null))
                ->setData('manager', (isset($data['manager']) ? (int) $data['manager'] : 0))
                ->setData('position', (isset($data['position']) ? (int) $data['position'] : 0))
                ->setData('points', (isset($data['points']) ? (int) $data['points'] : 0))
                ->save();
            
            $this->messageManager->addSuccess(__('Item has been saved.'));
		} catch(\Exception $e) {
			$this->messageManager->addError(__('An error has occurred: %1', $e->getMessage()));
		}
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }
    
}
