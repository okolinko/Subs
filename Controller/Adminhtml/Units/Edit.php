<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 8:34 PM
 */

namespace Toppik\Subscriptions\Controller\Adminhtml\Units;


use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Toppik\Subscriptions\Model\Settings\Unit;

class Edit extends Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return Page
     */
    protected function _initAction() {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Toppik_Subscriptions::subscriptions_settings_units');
        $resultPage->addBreadcrumb(__('Subscriptions'), __('Subscriptions'));
        $resultPage->addBreadcrumb(__('Settings'), __('Settings'));
        return $resultPage;
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('unit_id');
        /* @var $model Unit */
        $model = $this->_objectManager->create('Toppik\Subscriptions\Model\Settings\Unit');
        if($id) {
            $model->load($id);
            if(! $model->getId()) {
                $this->messageManager->addError(__('This unit no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if(! empty($data)) {
            $model->addData($data);
        }

        $this->registry->register('unit', $model);

        $resultPage = $this->_initAction();

        if($id) {
            $resultPage->addBreadcrumb(__('Edit Unit'), __('Edit Unit'));
        } else {
            $resultPage->addBreadcrumb(__('New Unit'), __('New Unit'));
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Units'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Unit'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_settings_units');
    }

}