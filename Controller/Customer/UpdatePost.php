<?php
namespace Toppik\Subscriptions\Controller\Customer;

class UpdatePost extends AbstractController {
    
    public function execute() {
		/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		
        if(!$this->_customerSession->isLoggedIn()) {
            return $resultRedirect->setPath('*/*/');
        }
        
        if(!$this->_formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }
		
        $redirectUrl = null;
		
        try {
			$model = $this->_initProfile();
			
			if(!$model) {
				$this->messageManager->addError(__('This profile no longer exists.'));
				return $resultRedirect->setPath('*/*');
			}
			
            $redirectUrl = $this->_buildUrl('*/*/view', ['_secure' => true, 'id' => $model->getId()]);
            
			$updated 	= false;
			$type 		= trim(strtolower($this->getRequest()->getParam('action_type')));
            $note       = strip_tags(trim($this->getRequest()->getParam('note')));
            
            switch($type) {
                case 'quantity':
					if($model->canEditQuantity() === true) {
						$updated = $model->changeQuantity($this->getRequest()->getPost('item_qty'));
					}
					
                    break;
                
                case 'frequency':
					if($model->canEditFrequency() === true) {
						$updated = $model->changeFrequency($this->getRequest()->getPost('unit_id'));
					}
					
                    break;
                
                case 'cc':
					if($model->canEditCc() === true) {
						$updated = $model->changeCc($this->getRequest()->getPost('gateway_token'));
					}
					
                    break;
                
                case 'next_order_date':
                    if($model->canEditNextDate() !== true) {
                        $this->messageManager->addError(__('Unable to change next order date'));
                        return $resultRedirect->setPath('subscriptions/customer/view', ['id' => $model->getId()]);
                    }
                    
                    /* if(empty($note)) {
                        $this->messageManager->addError(__('Can\'t change next order date profile without reason. Please specify reason.'));
                        return $resultRedirect->setPath('subscriptions/customer/view', ['id' => $model->getId()]);
                    } */
                    
                    $model->changeNextOrderDate($this->getRequest()->getParam('date'), $model->getStoreId(), $note);
                    $this->messageManager->addSuccess(__('Subscription successfully updated'));
                    $updated = true;
                    break;
                
                case 'product':
                    if($model->canEditProduct() !== true) {
                        $this->messageManager->addError(__('Unable to change product'));
                        return $resultRedirect->setPath('subscriptions/customer/view', ['id' => $model->getId()]);
                    }
                    
                    $this->_productAction($model);
                    $updated = true;
                    break;
                
                default:
                    throw new \Exception('Wrong params');
                    break;
            }
			
			if($updated === true) {
				$this->messageManager->addSuccess(__('The profile has been updated.'));
			}
			
            $url = $this->_buildUrl('*/*/view', ['_secure' => true, 'id' => $model->getId()]);
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->success($url));
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch(\Exception $e) {
            $redirectUrl = $this->_buildUrl('*/*/index', ['_secure' => true]);
            $this->messageManager->addException($e, $e->getMessage());
        }
		
        $url = $redirectUrl;
		
        if(!$redirectUrl) {
            // $this->_getSession()->setAddressFormData($this->getRequest()->getPostValue());
            $url = $this->_buildUrl('*/*/view', ['id' => (int) $this->getRequest()->getParam('id'), 'type' => $type]);
        }
		
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
    }
	
    protected function _productAction($model) {
        $removed    = array();
        $remove     = array();
        $qty        = 1;
        $product_id = (int) $this->getRequest()->getParam('product');
        
        if($product_id < 1) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please choose product'));
        }
        
        $subscription_helper = $this->_objectManager->create('Toppik\Subscriptions\Helper\Data');
        
        foreach($model->getAllVisibleItems() as $_item) {
            if((int) $_item->getData('is_onetime_gift') !== 1) {
                $remove[] = $_item;
                
                if($_item->getQty()) {
                    $qty = max(1, (int) $_item->getQty());
                }
            }
        }
        
        $price      = null;
        $product    = $this->_objectManager->create('Magento\Catalog\Model\Product')->setStoreId($model->getStoreId())->load($product_id);
        
        if(!$product->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Product ID %1 does not exist', $product_id));
        }
        
        if(!$subscription_helper->productHasSubscription($product)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Product # %1 does not have subscription', $product->getSku()));
        }
        
        $subscription = $subscription_helper->getSubscriptionByProduct($product);
        
        foreach($subscription->getItemsCollection() as $_item) {
            if($model->getFrequencyLength() == ($_item->getPeriod()->getLength() * $_item->getUnit()->getLength())) {
                $price = $_item->getRegularPrice();
                break;
            }
        }
        
        if($price === null) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot find subscription price for product # %1', $product->getSku()));
        }
        
        $item = $model->addProduct($product, $price, $qty, array(), false);
        
        if($item === null) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to add product'));
        }
        
        $message = __( 
            'Added item(s): %1',
            sprintf('%s (%s)', $item->getSku(), $price)
        );
        
        $model->setStatusHistoryCode('product');
        $model->setStatusHistoryMessage($message);
        $model->save();
        
        $this->messageManager->addSuccess($message);
        
        /* Remove all subscription items except gifts */
        foreach($remove as $_item) {
            $_item->delete();
            
            if($_item->getHasChildren()) {
                foreach($_item->getChildren() as $_child) {
                    $_child->delete();
                }
            }
            
            $removed[] = $_item->getSku();
        }
        
        $message = __( 
            'Removed item(s): %1',
            implode(', ', array_unique($removed))
        );
        
        $model->updateProfile();
        
        $model->setStatusHistoryCode('product');
        $model->setStatusHistoryMessage($message);
        $model->save();
        
        $this->messageManager->addSuccess($message);
    }
    
}
