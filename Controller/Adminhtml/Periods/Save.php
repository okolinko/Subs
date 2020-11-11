<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 2:13 PM
 */

namespace Toppik\Subscriptions\Controller\Adminhtml\Periods;


use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Toppik\Subscriptions\Model\Settings\Period;

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
            /** @var Period $model */
            $model = $this->_objectManager->create('Toppik\Subscriptions\Model\Settings\Period');

            $id = $this->getRequest()->getParam('period_id');
            if($id) {
                $model->load($id);
            }

            $model->addData($data);
            $this->_eventManager->dispatch(
                $model->getEventPrefix() . '_prepare_save',
                ['period' => $model, 'request' => $this->getRequest(), ]
            );

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved Period.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')
                    ->setFormData(false);
                if($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['period_id' => $model->getId(), '_current' => true, ]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch(LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch(\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch(\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the period.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['period_id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_settings_periods');
    }
}