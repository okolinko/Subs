<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Add;

class Delete extends \Toppik\Subscriptions\Controller\Adminhtml\Add\AbstractClass {

    public function execute() {
		try {
            $id     = (int) $this->getRequest()->getParam('id');
            $model  = $this->_objectManager->create('\Toppik\Subscriptions\Model\Profile\Add');

            if($id) {
                $model->load($id);
            }

            if(!$model->getId()) {
                throw new \Exception(__('The item ID "%1" no longer exists.', $id));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }

			$product = $this->_productRepository->get($model->getSku());
			if ($product->getTypeId() == 'simple') {
				try {
					$product->setSubscriptionProductAddHash('')->save();
				} catch (\Exception $e) {
					$this->messageManager->addError(__('An error has occurred: %1', $e->getMessage()));
				}
			}

            $model->delete();

            $this->messageManager->addSuccess(__('Item has been deleted.'));
		} catch(\Exception $e) {
			$this->messageManager->addError(__('An error has occurred: %1', $e->getMessage()));
		}

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

}
