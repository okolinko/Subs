<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 4:07 PM
 */

namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;


use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Toppik\Subscriptions\Model\Profile;

class Suspend extends Action {

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('profile_id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if($id) {
            try {
                /* @var Profile $model */
                $model = $this->_objectManager->create('Toppik\Subscriptions\Model\Profile');
                $model->load($id);
                
                if(!$model->canSuspend()) {
                    throw new LocalizedException(__('Unable to suspend profile, not allowed.'));
                }
                
				$model->changeStatusToSuspend(__('Status changed by admin'), \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_MANUAL_ADMIN);
                $this->messageManager->addSuccess(__('The profile has been suspended.'));
                return $resultRedirect->setPath('*/*/all');
            } catch(LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/all', ['period_id' => $id, ]);
            } catch(\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while suspending the profile.'));
                return $resultRedirect->setPath('*/*/all', ['period_id' => $id, ]);
            }
        }
        $this->messageManager->addError(__('We can\'t find profile to suspend.'));
        return $resultRedirect->setPath('*/*/all');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_profiles_activate');
    }
}