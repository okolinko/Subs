<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Add;

class Save extends \Toppik\Subscriptions\Controller\Adminhtml\Add\AbstractClass {

    public function execute() {
		try {
            $data       = $this->getRequest()->getPost();
            $id         = isset($data['id']) ? (int) $data['id'] : null;
            $model      = $this->_objectManager->create('\Toppik\Subscriptions\Model\Profile\Add');

            if($id) {
                $model->load($id);

                if(!$model->getId()) {
                    throw new \Exception(__('The item ID %1 no longer exists.', $id));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $sku    = isset($data['sku']) ? $data['sku'] : null;
            $price  = isset($data['price']) ? (float) $data['price'] : 0;
            $qty    = isset($data['qty']) ? (int) $data['qty'] : 1;
            $status = isset($data['status']) ? (int) $data['status'] : 0;

            if(!$sku || $price < 0 || !$qty) {
                throw new \Exception(__('Invalid data provided'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }

            if(!in_array($status, array_keys($model->getAvailableStatus()))) {
                throw new \Exception(__('Unknown status ID: %1', $status));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }


            $model
                ->setData('sku', $sku)
                ->setData('price', $price)
                ->setData('qty', $qty)
                ->setData('status', $status)
                ->setData('public_hash', $model->generatePublicHash())
                ->save();

			$product = $this->_productRepository->get($sku);
			if ($product->getTypeId() == 'simple') {
				try {
					$product->setSubscriptionProductAddHash($model->generatePublicHash())->save();
				} catch (\Exception $e) {
					$this->messageManager->addError(__('An error has occurred: %1', $e->getMessage()));
				}
			}

            $this->messageManager->addSuccess(__('Item has been saved.'));
		} catch(\Exception $e) {
			$this->messageManager->addError(__('An error has occurred: %1', $e->getMessage()));
		}

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

}
