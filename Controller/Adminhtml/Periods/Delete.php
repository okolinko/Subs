<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 4:07 PM
 */

namespace Toppik\Subscriptions\Controller\Adminhtml\Periods;


use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;

class Delete extends Action {

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('period_id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if($id) {
            try {
                $model = $this->_objectManager->create('Toppik\Subscriptions\Model\Settings\Period');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The period has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch(\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['period_id' => $id, ]);
            } catch(\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while deleting the period.'));
                return $resultRedirect->setPath('*/*/edit', ['period_id' => $id, ]);
            }
        }
        $this->messageManager->addError(__('We can\'t find period to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_settings_periods');
    }
}