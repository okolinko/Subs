<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 2:13 PM
 */

namespace Toppik\Subscriptions\Controller\Adminhtml\Subscriptions;


use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Toppik\Subscriptions\Model\Settings\Subscription;

class Save extends Action
{

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if($data) {
            /** @var Subscription $model */
            $model = $this->_objectManager->create('Toppik\Subscriptions\Model\Settings\Subscription');

            $id = $this->getRequest()->getParam('subscription_id');
            if($id) {
                $model->load($id);
            }

            $model->addData($data);
            if(isset($data['product_id'])) {
                $model->setProductId($data['product_id']);
            }

            $itemsCollection = $model->getItemsCollection();

            $itemsData = $this->getRequest()->getParam('item_listing');

            if(isset($itemsData['items'])) {
                $itemsData = $itemsData['items'];
            }

            if(is_array($itemsData)) {
                foreach($itemsCollection as $item) {
                    /* @var \Toppik\Subscriptions\Model\Settings\Item $item */
                    $item->isDeleted(true);
                }
                foreach($itemsData as $itemData) {
                    if(isset($itemData['item_id']) and is_string($itemData['item_id'])) {
                        $itemId = (int) $itemData['item_id'];
                        unset($itemData['item_id']);
                        $found = false;
                        foreach($itemsCollection as $item) {
                            if($item->getId() and (int) $item->getId() === $itemId) {
                                $item->addData($itemData);
                                $found = true;
                                $item->isDeleted(false);
                            }
                        }
                        if(! $found) {
                            $item = $this->_objectManager->create('Toppik\Subscriptions\Model\Settings\Item');
                            $item->addData($itemData);
                            $itemsCollection->addItem($item);
                        }
                    }
                }
            }

            $this->_eventManager->dispatch(
                $model->getEventPrefix() . '_prepare_save',
                ['subscription' => $model, 'request' => $this->getRequest(), ]
            );

            try {
                $model->save();
                $model->saveItems();
                $this->messageManager->addSuccess(__('You saved Subscription.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')
                    ->setFormData(false);
                if($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['subscription_id' => $model->getId(), '_current' => true, ]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch(LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch(\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch(\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the subscription: %1', $e->getMessage()));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['subscription_id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_settings_subscriptions');
    }
}