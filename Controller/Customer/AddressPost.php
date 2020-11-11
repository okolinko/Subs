<?php
namespace Toppik\Subscriptions\Controller\Customer;

class AddressPost extends \Magento\Customer\Controller\Address\FormPost {
    
    public function execute() {
		/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		$session = $this->_objectManager->get('\Magento\Customer\Model\Session');
		
        if(!$this->_formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }
		
        if(!$this->getRequest()->isPost()) {
            return $resultRedirect->setPath('*/*/');
        }
		
        if(!$session->isLoggedIn()) {
            return $resultRedirect->setPath('*/*/');
        }
		
        $redirectUrl = null;
		
        try {
			$id = (int) $this->getRequest()->getParam('id');
			
			if(!$id) {
				return $resultRedirect->setPath('*/*/');
			}
			
			$model = $this->_objectManager->create('Toppik\Subscriptions\Model\Profile')->load($id);
			
			if(!$model->getId()) {
				return $resultRedirect->setPath('*/*/');
			}
			
			if((int) $model->getCustomerId() !== (int) $session->getCustomerId()) {
				return $resultRedirect->setPath('*/*/');
			}
			
			$updated    = false;
            $messages   = array();
            $newData    = $this->_extractAddress();
			$type       = trim(strtolower($this->getRequest()->getParam('type')));
            
			if(count($newData) > 0) {
				switch($type) {
					case 'billing':
                        if(!$model->canEditBillingAddress()) {
                            $this->messageManager->addError(__('Can\'t edit billing address of current profile'));
                            
                            return $this->resultRedirectFactory->create()->setUrl(
                                $this->_redirect->error($this->_buildUrl('*/*/view', ['_secure' => true, 'id' => $model->getId()]))
                            );
                        }
                        
						$address = $model->getBillingAddress();
						
						foreach($newData as $_key => $_value) {
							if(is_array($_value)) {
								$_value = trim(implode("\n", $_value));
							}
							
							if(is_scalar($_value)) {
								if($address->hasData($_key) && $address->getData($_key) != $_value) {
                                    $messages[] = __('Billing %1 changed from "%2" to "%3"', $_key, $address->getData($_key), $_value);
									$address->setData($_key, $_value);
									$updated = true;
								}
							}
						}
                        
						break;
                    
					case 'shipping':
                        if(!$model->canEditShippingAddress()) {
                            $this->messageManager->addError(__('Can\'t edit shipping address of current profile'));
                            
                            return $this->resultRedirectFactory->create()->setUrl(
                                $this->_redirect->error($this->_buildUrl('*/*/view', ['_secure' => true, 'id' => $model->getId()]))
                            );
                        }
                        
						$address = $model->getShippingAddress();
						
						foreach($newData as $_key => $_value) {
							if(is_array($_value)) {
								$_value = trim(implode("\n", $_value));
							}
							
							if(is_scalar($_value)) {
								if($address->hasData($_key) && $address->getData($_key) != $_value) {
                                    $messages[] = __('Shipping %1 changed from "%2" to "%3"', $_key, $address->getData($_key), $_value);
									$address->setData($_key, $_value);
									$updated = true;
								}
							}
						}
                        
						break;
                    
					default:
                        $this->messageManager->addError(__('Wrong params'));
                        
                        return $this->resultRedirectFactory->create()->setUrl(
                            $this->_redirect->error($this->_buildUrl('*/*/view', ['_secure' => true, 'id' => $model->getId()]))
                        );
				}
			}
			
			if($updated === true) {
                if(count($messages)) {
                    $model->setStatusHistoryCode('address_change');
                    $model->setStatusHistoryMessage(implode(', ', $messages));
                }
                
                $model->save();
                
				$this->messageManager->addSuccess(__('You updated the address.'));
			}
			
            $url = $this->_buildUrl('*/*/view', ['_secure' => true, 'id' => $model->getId()]);
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->success($url));
        } catch(InputException $e) {
            $this->messageManager->addError($e->getMessage());
            foreach($e->getErrors() as $error) {
                $this->messageManager->addError($error->getMessage());
            }
        } catch(\Exception $e) {
            $redirectUrl = $this->_buildUrl('*/*/view', ['_secure' => true, 'id' => $model->getId()]);
            $this->messageManager->addException($e, $e->getMessage());
        }
		
        $url = $redirectUrl;
		
        if(!$redirectUrl) {
            $this->_getSession()->setAddressFormData($this->getRequest()->getPostValue());
            $url = $this->_buildUrl('*/*/address', ['id' => $this->getRequest()->getParam('id'), 'type' => $type]);
        }
		
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
    }
	
    /**
     * Extract address from request
     *
     * @return array
     */
    protected function _extractAddress() {
        /** @var \Magento\Customer\Model\Metadata\Form $addressForm */
        $addressForm = $this->_formFactory->create('customer_address', 'customer_address_edit', []);
        $addressData = $addressForm->extractData($this->getRequest());
		
        $this->updateRegionData($addressData);
		
		unset($addressData['id']);
		unset($addressData['type']);
		
		return $addressData;
    }
    
    /**
     * Update region data
     *
     * @param array $attributeValues
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateRegionData(&$attributeValues) {
        if(!empty($attributeValues['region_id'])) {
            $newRegion = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }
    }
	
    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _buildUrl($route = '', $params = []) {
        /** @var \Magento\Framework\UrlInterface $urlBuilder */
        $urlBuilder = $this->_objectManager->create('Magento\Framework\UrlInterface');
        return $urlBuilder->getUrl($route, $params);
    }
    
}
