<?php
namespace Toppik\Subscriptions\Controller\Add;

class Cart extends \Toppik\Subscriptions\Controller\Add\AbstractController {

    public function execute() {
        $public_hash = $this->getRequest()->getParam('h');

        if(!$this->_customerSession->isLoggedIn() || !$this->_customerSession->getCustomerId()) {
            return $this->resultRedirectFactory
                        ->create()
                        ->setPath(
                            'customer/account/login',
                            array(
                                'referer' => base64_encode($this->_buildUrl('subscriptions/add/index', array('h' => $public_hash)))
                            )
                        );
        }

        try {
            $profile        = $this->_getProfile();
            $model          = $this->_getAddItem($public_hash);
//            $product        = $this->productHelper->initProduct($this->_getProductBySku($model->getSku())->getId(), $this);
            $product = $this->productRepository->get($model->getSku(), false, $this->_storeManager->getStore()->getId());

            if(!$product || !is_object($product)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Product not found!'));
            }

			$this->_coreRegistry->register('product', $product);
			$this->_coreRegistry->register('current_product', $product);

            $profile->addProduct($product, $model->getPrice(), $model->getQty(), array(), true);
            $profile->save();
            $this->messageManager->addSuccess(__('Product "%1" with price "%2" and qty "%3" has been added to profile # "%4"', $product->getName(), $model->getPrice(), $model->getQty(), $profile->getId()));
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
        } catch(\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add product to your subscription right now'));
        }

        return $this->resultRedirectFactory->create()->setPath('subscriptions/customer/view', array('id' => $profile->getId()));
    }

}
