<?php
namespace Toppik\Subscriptions\Controller\Customer;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Orders extends Action\Action
{
	
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
	
    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
	
    /**
     * Order view page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		
        $id 			= (int) $this->getRequest()->getParam('id');
        $model 			= $this->_objectManager->create('Toppik\Subscriptions\Model\Profile')->load($id);
		$session 		= $this->_objectManager->get('\Magento\Customer\Model\Session');
		$registry 		= $this->_objectManager->get('Magento\Framework\Registry');
		
		if(!$model->getId()) {
			$this->messageManager->addError(__('This profile no longer exists.'));
			return $resultRedirect->setPath('*/*');
		}
		
		if((int) $model->getCustomerId() !== (int) $session->getCustomerId()) {
			$this->messageManager->addError(__('This profile no longer exists.'));
			return $resultRedirect->setPath('*/*');
		}
		
        $registry->register('current_profile', $model);
		
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
		
        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
		
        if ($navigationBlock) {
            $navigationBlock->setActive('subscriptions/customer/index');
        }
		
        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
		
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
		
        return $resultPage;
    }
	
}
