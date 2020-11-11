<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;

class UpdatePost extends \Magento\Backend\App\Action {
    
    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_formFactory;
    
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;
    
    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 101.1.0
     */
    protected $serializer;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->_formFactory = $formFactory;
        $this->_regionFactory = $regionFactory;
        $this->serializer = $serializer;
        parent::__construct($context);
    }
    
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if(!$this->getRequest()->isPost()) {
            return $resultRedirect->setPath('*/*/all');
        }
        
        try {
            $id     = (int) $this->getRequest()->getParam('profile_id');
            $model  = $this->_objectManager->create('Toppik\Subscriptions\Model\Profile');
            
            if($id) {
                $model->load($id);
            }
            
			if(!$model->getId()) {
                $this->messageManager->addError(__('This profile no longer exists.'));
                return $resultRedirect->setPath('*/*/all');
			}
            
			$type           = trim(strtolower($this->getRequest()->getParam('action_type')));
            $note           = strip_tags(trim($this->getRequest()->getParam('note')));
            $note_option    = strip_tags(trim($this->getRequest()->getParam('note_option')));
            
            switch($type) {
                case 'quantity':
					if($model->canEditQuantity() === true) {
						$model->changeQuantity($this->getRequest()->getPost('item_qty'));
                        $this->messageManager->addSuccess(__('The profile has been updated.'));
					}
					
                    break;
                
                case 'frequency':
					if($model->canEditFrequency() === true) {
						$model->changeFrequency($this->getRequest()->getPost('unit_id'));
                        $this->messageManager->addSuccess(__('The profile has been updated.'));
					}
					
                    break;
                
                case 'cc':
					if($model->canEditCc() === true) {
						$model->changeCc($this->getRequest()->getPost('gateway_token'));
                        $this->messageManager->addSuccess(__('The profile has been updated.'));
					}
					
                    break;
                
                case 'cancel':
					if($model->canCancel() !== true) {
                        $this->messageManager->addError(__('Can\'t cancel profile'));
                        return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
					}
                    
                    $message = '';
                    $reasons = $this->_objectManager->get('Toppik\Subscriptions\Model\Settings\Reason');
                    
                    $option_ids     = $this->getRequest()->getParam('disposition_items');
                    $authSession    = $this->_objectManager->get('Magento\Backend\Model\Auth\Session');
                    
                    if(!is_array($option_ids) && (int) $option_ids == $option_ids) {
                        $option_ids = array($option_ids);
                    }
                    
                    if(!is_array($option_ids) || count($option_ids) < 1) {
                        $this->messageManager->addError(__('Can\'t cancel profile without disposition item(s). Please select one.'));
                        return $resultRedirect->setPath('*/*/cancel', ['profile_id' => $model->getId()]);
                    }
                    
                    foreach($reasons->getAllOptions() as $_option) {
                        if(isset($_option['value']) && isset($_option['label'])) {
                            if($_option['value'] == $note_option) {
                                $message = $_option['label'];
                                break;
                            }
                        }
                    }
                    
                    if(empty($message)) {
                        $this->messageManager->addError(__('Can\'t cancel profile without reason. Please specify reason.'));
                        return $resultRedirect->setPath('*/*/cancel', ['profile_id' => $model->getId()]);
                    }
                    
                    $model->changeStatusToCancel(__('Status changed by admin'), $message);
                    
                    $this->messageManager->addSuccess(__('The profile has been canceled.'));
                    
                    foreach($option_ids as $_option_id) {
                        $this->_objectManager->create('Toppik\Subscriptions\Model\Profile\Cancelled')
                            ->setData('profile_id', $model->getId())
                            ->setData('option_id', $_option_id)
                            ->setData('admin_id', $authSession->getUser()->getId())
                            ->setData('ip', (isset($_SERVER['REMOTE_ADDR']) ? ip2long($_SERVER['REMOTE_ADDR']) : null))
                            ->setData('message', $message)
                            ->save();
                    }
                    
                    $reportHelper = $this->_objectManager->create('Toppik\Subscriptions\Helper\Report');
                    $eventManager = $this->_objectManager->create('Magento\Framework\Event\ManagerInterface');
                    
                    try {
                        $title  = array();
                        $sku    = array();
                        
                        foreach($model->getAllVisibleItems() as $_item) {
                            if((int) $_item->getData('is_onetime_gift') !== 1) {
                                $title[] = $_item->getName();
                                $sku[] = $_item->getSku();
                            }
                        }

                        $template = $reportHelper->getSaveCancelEmailTemplate();
                        
                        $vars = array(
                            'profile'   => $model,
                            'customer'  => $model->getCustomer(),
                            'next_date' => date('m/d/Y', strtotime($model->getNextOrderAt())),
                            'sku'       => implode(', ', $sku),
                            'title'     => implode(', ', $title)
                        );
                        
                        $reportHelper->sendEmail($template, $model->getCustomer()->getEmail(), $model->getStoreId(), $vars);
                    } catch(\Exception $e) {
                        $message = sprintf('CANNOT send email on subscription_cancel for subscription ID %s: %s', $model->getId(), $e->getMessage());
                        
                        $eventManager->dispatch(
                            'toppikreport_system_add_message',
                            [
                                'entity_type' 	=> 'subscription_cancel',
                                'entity_id' 	=> $model->getId(),
                                'message' 		=> $message
                            ]
                        );
                        
                        $reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
                    }
                    
                    break;
                
                case 'next_order_date':
                    if($model->canEditNextDate() !== true) {
                        $this->messageManager->addError(__('Unable to change next order date'));
                        return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
                    }
                    
                    if(empty($note)) {
                        $this->messageManager->addError(__('Can\'t change next order date profile without reason. Please specify reason.'));
                        return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
                    }

                    $model->changeNextOrderDate($this->getRequest()->getParam('date'), $model->getStoreId(), $note);
                    
                    $this->messageManager->addSuccess(__('Subscription successfully updated'));
                    
                    break;
                
                case 'save_the_sale':
                    $this->_saveTheSaleAction($model);
                    break;
                
                case 'shipping':
                    $this->_shippingAddressAction($model);
                    break;
                
                case 'product':
                    $this->_productAction($model);
                    break;
                
                default:
                    throw new \Exception('Wrong params');
                    break;
            }
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch(\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while updating the profile: %1', $e->getMessage()));
        }
        
        if(isset($model) && $model->getId()) {
            return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
        }
        
        return $resultRedirect->setPath('*/*/all');
    }
	
    protected function _shippingAddressAction($model) {
        if(!$model->canEditShippingAddress()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Can\'t edit shipping address of current profile'));
        }
        
        $messages   = array();
        $newData    = $this->_extractAddress();
        $address    = $model->getShippingAddress();
        
        if(!isset($newData['region']) || !isset($newData['region_id']) || (empty($newData['region']) && empty($newData['region_id']))) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please choose State/Province'));
        }
        
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
        
        if(count($messages)) {
            $model->setStatusHistoryCode('address_change');
            $model->setStatusHistoryMessage(implode(', ', $messages));
            $model->save();
            
            $this->messageManager->addSuccess(__('You updated the address.'));
        }
    }
    
    protected function _productAction($model) {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $removed    = array();
        $remove     = array();
        $values     = array();
        $qty        = 1;
        $products   = $this->getRequest()->getParam('product');
        
        if(!is_array($products) || count($products) < 1) {
            $this->messageManager->addError(__('Please choose product'));
            return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
        }
        
        $subscription_helper = $this->_objectManager->create('Toppik\Subscriptions\Helper\Data');
        
        foreach($model->getAllItems() as $_item) {
            if((int) $_item->getData('is_onetime_gift') !== 1) {
                $remove[] = $_item;
                
                if($_item->getQty()) {
                    $qty = max(1, (int) $_item->getQty());
                }
            }
        }
        
        foreach($products as $_product_id => $_options) {
            $price      = null;
            $product    = $this->_objectManager->create('Magento\Catalog\Model\Product')->setStoreId($model->getStoreId())->load($_product_id);
            
            if(!$product->getId()) {
                $this->messageManager->addError(__('Product ID %1 does not exist', $_product_id));
                return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
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
            
            // $_options['qty'] = $qty ;
            
            $value = $this->_addProduct($model, $_product_id, $product, $_options, $price, false);
            $values[] = sprintf('%s (%s)', $value, $price);
        }
        
        $message = __( 
            'Added item(s): %1',
            implode(', ', $values)
        );
        
        $model->setStatusHistoryCode('product');
        $model->setStatusHistoryMessage($message);
        $model->save();
        
        $this->messageManager->addSuccess($message);
        
        /* Remove all subscription items except gifts */
        foreach($remove as $_item) {
            $_item->delete();
            $removed[] = $_item->getSku();
        }
        
        $message = __( 
            'Removed item(s): %1',
            implode(', ', array_unique($removed))
        );
        
        $grand_total        = 0;
        $base_ground_total  = 0;
        $items_count        = 0;
        $items_qty          = 0;
        
        foreach($model->getAllVisibleItems() as $_item) {
            $grand_total        = $grand_total + $_item->getData('row_total');
            $base_ground_total  = $base_ground_total + $_item->getData('base_row_total');
            $items_count        = $items_count + 1;
            $items_qty          = $items_qty + $_item->getQty();
        }
        
        $model->setGrandTotal($grand_total);
        $model->setBaseGrandTotal($base_ground_total);
        $model->setItemsCount($items_count);
        $model->setItemsQty($items_qty);
        
        $model->setStatusHistoryCode('product');
        $model->setStatusHistoryMessage($message);
        $model->save();
        
        $this->messageManager->addSuccess($message);
    }
    
    protected function _saveTheSaleAction($model) {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $points             = 0;
        $labels             = array();
        $values             = array();
        
        $entities           = array();
        $addedCoupon        = false;
        $addedProduct       = false;
        
        $reportHelper       = $this->_objectManager->create('Toppik\Subscriptions\Helper\Report');
        $authSession        = $this->_objectManager->get('Magento\Backend\Model\Auth\Session');
        $eventManager       = $this->_objectManager->create('Magento\Framework\Event\ManagerInterface');
        $collection         = $this->_objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Points\Collection');
        
        $earnedPoints       = 0;
        $maxOnetimePoints   = $reportHelper->getMaxOnetimePoints();
        $availablePoints    = $maxOnetimePoints;
        
        $lastItem           = null;
        
        try {
            $note               = strip_tags(trim($this->getRequest()->getParam('note')));
            $options            = $this->getRequest()->getParam('options');
            $option             = $this->getRequest()->getParam('option');
            
            if(!is_array($options) || count($options) < 1) {
                $this->messageManager->addError(__('Please choose an option!'));
                return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
            }
            
            $collection->addFieldToFilter('id', array('in' => $options));
            
            if(count($options) !== count($collection->getItems())) {
                $this->messageManager->addError(
                    __('Invalid option count: sent %1 item(s) but found %2 item(s)', count($options), count($collection->getItems()))
                );
                
                return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
            }
            
            foreach($collection->getItems() as $_item) {
                $points = $points + $_item->getPoints();
            }
            
            if($points > $availablePoints) {
                $this->messageManager->addError(__('Selected options exceed number of available points!'));
                return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
            }
            
            foreach($collection->getItems() as $_item) {
                $value = null;
                $labels[] = $_item->getTitle();
                
                $subscriptionPoints = $_item->getPoints();
                $adminPoints = 0;
                $earnedPoints = $earnedPoints + $adminPoints;
                
                $availablePoints = max(0, ($availablePoints - $subscriptionPoints));
                
                if((int) $_item->getData('type_id') === \Toppik\Subscriptions\Model\Profile\Points::TYPE_PRODUCT) {
                    $products = null;
                    
                    if(isset($option[$_item->getId()]) && isset($option[$_item->getId()]['product'])) {
                        if(is_array($option[$_item->getId()]['product']) && count($option[$_item->getId()]['product'])) {
                            $products = $option[$_item->getId()]['product'];
                        }
                    }
                    
                    if(!is_array($products) || count($products) < 1) {
                        $this->messageManager->addError(__('Please choose product for option ID %1', $_item->getId()));
                        return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
                    }
                    
                    foreach($products as $_product_id => $_options) {
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->setStoreId($model->getStoreId())->load($_product_id);
                        
                        if(!$product->getId()) {
                            $this->messageManager->addError(__('Product ID %1 does not exist', $_product_id));
                            return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
                        }
                        
                        $value = $this->_addProduct($model, $_product_id, $product, $_options);
                        $values[] = $value;
                        $addedProduct = true;
                        
                        if(!isset($entities[\Toppik\Subscriptions\Model\Profile\Points::TYPE_PRODUCT])) {
                            $entities[\Toppik\Subscriptions\Model\Profile\Points::TYPE_PRODUCT] = array();
                        }
                        
                        $entities[\Toppik\Subscriptions\Model\Profile\Points::TYPE_PRODUCT][] = $value;
                    }
                } else if((int) $_item->getData('type_id') === \Toppik\Subscriptions\Model\Profile\Points::TYPE_COUPON) {
                    $coupon = null;
                    
                    if(isset($option[$_item->getId()]) && isset($option[$_item->getId()]['coupon'])) {
                        $coupon = $option[$_item->getId()]['coupon'];
                    }
                    
                    if($coupon === null || empty($coupon)) {
                        $this->messageManager->addError(__('Please choose coupon for option ID %1', $_item->getId()));
                        return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
                    }
                    
                    $value = $coupon;
                    $values[] = $value;
                    $addedCoupon = true;
                    
                    if(!isset($entities[\Toppik\Subscriptions\Model\Profile\Points::TYPE_COUPON])) {
                        $entities[\Toppik\Subscriptions\Model\Profile\Points::TYPE_COUPON] = array();
                    }
                    
                    $entities[\Toppik\Subscriptions\Model\Profile\Points::TYPE_COUPON][] = $value;
                } else if((int) $_item->getData('type_id') === \Toppik\Subscriptions\Model\Profile\Points::TYPE_PRODUCT_PRICE) {
                    $price = null;
                    
                    if(isset($option[$_item->getId()]) && isset($option[$_item->getId()]['price'])) {
                        $price = (float) $option[$_item->getId()]['price'];
                    }
                    
                    if($price === null || empty($price) || $price < 0.001) {
                        $this->messageManager->addError(__('Please type price for option ID %1', $_item->getId()));
                        return $resultRedirect->setPath('*/*/view', ['profile_id' => $model->getId()]);
                    }
                    
                    $model->changeProductPrice(null, $price);
                    
                    $value = $price;
                    $values[] = $value;
                }
                
                $saveItem = $this->_objectManager->create('Toppik\Subscriptions\Model\Profile\Save');
                
                $saveItem
                    ->setData('profile_id', $model->getId())
                    ->setData('option_id', $_item->getId())
                    ->setData('admin_id', $authSession->getUser()->getId())
                    ->setData('used_points', 0)
                    ->setData('admin_points', $adminPoints)
                    ->setData('subscription_points', $subscriptionPoints)
                    ->setData('value', $value)
                    ->setData('ip', (isset($_SERVER['REMOTE_ADDR']) ? ip2long($_SERVER['REMOTE_ADDR']) : null))
                    ->setData('message', $note)
                    ->save();
                
                $lastItem = $saveItem;
            }
            
            if($lastItem !== null) {
                $earnedPoints = $earnedPoints + $availablePoints;
                
                $lastItem
                    ->setData('used_points', $maxOnetimePoints)
                    ->setData('admin_points', $availablePoints)
                    ->save();
            }
            
            $message = __( 
                'Applied %1 option%2: %3%4',
                count($labels),
                count($labels) > 1 ? 's' : '',
                implode(', ', $labels),
                (count($values) > 0 ? __(' with item(s) %1', implode(', ', $values)) : '')
            );
            
            $model->setStatusHistoryCode('save_the_sale');
            $model->setStatusHistoryMessage($message);
            $model->save();
            
            $this->messageManager->addSuccess($message);
            $this->messageManager->addSuccess(__('You earned %1 point%2', $earnedPoints, ($earnedPoints > 1 ? 's' : '')));
        } catch(\Exception $e) {
            $message = sprintf('CANNOT send email on save_the_sale for subscription ID %s: %s', $model->getId(), $e->getMessage());
            
            $eventManager->dispatch(
                'toppikreport_system_add_message',
                [
                    'entity_type' 	=> 'save_the_sale',
                    'entity_id' 	=> $model->getId(),
                    'message' 		=> $message
                ]
            );
            
            $reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
        }
        
        try {
            if(count($entities) > 0) {
                $title  = array();
                $sku    = array();
                
                foreach($model->getAllVisibleItems() as $_item) {
                    if((int) $_item->getData('is_onetime_gift') !== 1) {
                        $title[] = $_item->getName();
                        $sku[] = $_item->getSku();
                    }
                }
                
                foreach($entities as $_type_id => $_entities) {
                    if(is_array($_entities) && count($_entities) > 0) {
                        foreach($_entities as $_entity) {
                            $template = null;
                            
                            if((int) $_type_id === \Toppik\Subscriptions\Model\Profile\Points::TYPE_PRODUCT) {
                                $template = $reportHelper->getSaveProductGiftEmailTemplate();
                            } else if((int) $_type_id === \Toppik\Subscriptions\Model\Profile\Points::TYPE_COUPON) {
                                $template = $reportHelper->getSaveCouponGiftEmailTemplate();
                            }
                            
                            if($template === null) {
                                throw new \Magento\Framework\Exception\LocalizedException(__('Email template does not exist for type ID %1', $_type_id));
                            }
                            
                            $vars = array(
                                'profile'   => $model,
                                'customer'  => $model->getCustomer(),
                                'next_date' => date('m/d/Y', strtotime($model->getNextOrderAt())),
                                'sku'       => implode(', ', $sku),
                                'title'     => implode(', ', $title),
                                'value'     => $_entity
                            );
                            
                            $reportHelper->sendEmail($template, $model->getCustomer()->getEmail(), $model->getStoreId(), $vars);
                        }
                    }
                }
            }
        } catch(\Exception $e) {
            $message = sprintf('CANNOT send email on save_the_sale for subscription ID %s: %s', $model->getId(), $e->getMessage());
            
            $eventManager->dispatch(
                'toppikreport_system_add_message',
                [
                    'entity_type' 	=> 'save_the_sale',
                    'entity_id' 	=> $model->getId(),
                    'message' 		=> $message
                ]
            );
            
            $reportHelper->log(sprintf('%s %s', str_repeat('=', 5), $message), [], \Toppik\Subscriptions\Logger\Logger::ERROR);
        }
    }
    
    protected function _addProduct($model, $product_id, $product, $options, $price = null, $is_gift = true) {
        try {
            $value      = null;
            $price      = $price !== null ? $price : (isset($options['price']) ? $options['price'] : 0);
            $qty        = isset($options['qty']) && $options['qty'] > 0 ? (int) $options['qty'] : 1;
            
            $item = $model->addProduct($product, $price, $qty, $options, $is_gift);
            
            if($item === null) {
                throw new \Exception(__('Unable to add product'));
            }
            
            $value = $item->getSku();
        } catch(\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/view', ['profile_id' => $model->getId()]);
        }
        
        return $value;
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
            $newRegion = $this->_regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }
    }
	
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_management');
    }
    
}
