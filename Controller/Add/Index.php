<?php
namespace Toppik\Subscriptions\Controller\Add;

class Index extends \Toppik\Subscriptions\Controller\Add\AbstractController {

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

            $profile->setIsAlreadyAddedProduct($this->_hasProduct($profile, $model->getSku()));

            $this->registry->register('subscriptions_add_model', $model);
            $this->registry->register('current_profile', $profile);
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('/');
        } catch(\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add product to your subscription right now'));
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');

        if($navigationBlock) {
            $navigationBlock->setActive('subscriptions/customer/index');
        }

        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');

        if($block) {
            $block->setRefererUrl($this->_buildUrl('subscriptions/customer/index'));
        }

        return $resultPage;
    }

}
