<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 4:07 PM
 */

namespace Toppik\Subscriptions\Controller\Adminhtml\Management;


use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Toppik\Subscriptions\Model\Profile;
use Toppik\Subscriptions\Model\ResourceModel\Profile\Collection;

class Skuchange extends Action {

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $sourceSku = $this->getRequest()->getParam('source_sku');
        $targetSku = $this->getRequest()->getParam('target_sku');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            if(empty($sourceSku) or empty($targetSku)) {
                throw new LocalizedException(__('Wrong input data.'));
            }
            /* @var Collection $profileCollection */
            $profileCollection = $this->_objectManager->create('Toppik\Subscriptions\Model\ResourceModel\Profile\Collection');
            $totalChanged = $profileCollection->changeSku($sourceSku, $targetSku);
            if(! $totalChanged) {
                $this->messageManager->addSuccess(__('No profiles with sku "' . $sourceSku . '" were found.'));
            } else {
                $this->messageManager->addSuccess(__('Changed sku from "' . $sourceSku . '" to "' . $targetSku . '" for ' . $totalChanged . ' profile(s).'));
            }
        } catch(LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch(\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while changing sku.'));
        }

        return $resultRedirect->setPath('*/*/sku');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_management_change_sku');
    }
}