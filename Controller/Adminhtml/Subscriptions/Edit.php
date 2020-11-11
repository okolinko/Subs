<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 8:34 PM
 */

namespace Toppik\Subscriptions\Controller\Adminhtml\Subscriptions;


use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Toppik\Subscriptions\Model\Settings\Subscription;

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
        $resultPage->setActiveMenu('Toppik_Subscriptions::subscriptions_settings_subscriptions');
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
        $id = $this->getRequest()->getParam('subscription_id');
        /* @var $model Subscription */
        $model = $this->_objectManager->create('Toppik\Subscriptions\Model\Settings\Subscription');
        if($id) {
            $model->load($id);
            if(! $model->getId()) {
                $this->messageManager->addError(__('This subscription no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if(! empty($data)) {
            $model->addData($data);
            if(isset($data['item_listing']['items'])) {
                $itemsData = $data['item_listing']['items'];
                $itemsCollection = $model->getItemsCollection();
                if(is_array($itemsData)) {
                    foreach($itemsData as $itemData) {
                        if(isset($itemData['item_id']) and is_string($itemData['item_id'])) {
                            $itemId = (int) $itemData['item_id'];
                            unset($itemData['item_id']);
                            $found = false;
                            foreach($itemsCollection as $item) {
                                if($item->getId() and (int) $item->getId() === $itemId) {
                                    $item->addData($itemData);
                                    $found = true;
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
            }
        }

        $this->registry->register('subscription', $model);

        $resultPage = $this->_initAction();

        if($id) {
            $resultPage->addBreadcrumb(__('Edit Subscription'), __('Edit Subscription'));
        } else {
            $resultPage->addBreadcrumb(__('New Subscription'), __('New Subscription'));
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Subscriptions'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Subscription'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_settings_subscriptions');
    }

}