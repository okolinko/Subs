<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Points;

class Coupon extends \Toppik\Subscriptions\Controller\Adminhtml\Points\AbstractClass {
	
    public function execute() {
		try {
            $coupon     = null;
            $errors     = array();
            
            $id         = (int) $this->getRequest()->getParam('id');
            $model      = $this->_objectManager->create('\Toppik\Subscriptions\Model\Profile\Points');
            
            if($id) {
                $model->load($id);
            }
            
            if(!$model->getId()) {
                $errors[] = __('The item ID %1 no longer exists.', $id);
            }
            
            if((count($errors) === 0) === true) {
                $coupon = $model->getCoupon();
            }
		} catch(\Exception $e) {
            $errors[] = __('An error has occurred: %1', $e->getMessage());
		}
        
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        
        return $resultJson->setData([
            'code'      => $coupon !== null ? $coupon->getCode() : null,
            'name'      => $coupon !== null ? $coupon->getName() : null,
            'errors'    => $errors
        ]);
    }
    
    /**
     * @return bool
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_management');
    }
	
}
