<?php
namespace Toppik\Subscriptions\Controller\Customer;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Tempsuspend extends Action\Action {
    
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
    public function execute() {
		/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		
        try {
			$id 		= (int) $this->getRequest()->getParam('id');
			$model 		= $this->_objectManager->create('Toppik\Subscriptions\Model\Profile')->load($id);
			$session 	= $this->_objectManager->get('\Magento\Customer\Model\Session');
			$dateTime 	= $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime');
			
			if(!$model->getId()) {
				$this->messageManager->addError(__('This profile no longer exists.'));
				return $resultRedirect->setPath('*/*');
			}
			
			if((int) $model->getCustomerId() !== (int) $session->getCustomerId()) {
				$this->messageManager->addError(__('This profile no longer exists.'));
				return $resultRedirect->setPath('*/*');
			}
			
			if($model->canSuspend() !== true) {
				$this->messageManager->addError(__('Unable suspend subscription'));
				return $resultRedirect->setPath('*/*');
			}
			
	        $period 	= (int) $this->getRequest()->getParam('period');
			$timestamp 	= strtotime("+$period hour");
			
			if($timestamp === false) {
				$this->messageManager->addError(__('Invalid date'));
				return $resultRedirect->setPath('*/*');
			}
			
	        $resumeDate = date("Y-m-d H:i:s", $timestamp);
			
			if(!$resumeDate) {
				$this->messageManager->addError(__('Invalid date'));
				return $resultRedirect->setPath('*/*');
			}
			
			$model->setResumeAt($resumeDate);
            
            $model->changeStatusToSuspend(__('Status changed by customer'), \Toppik\Subscriptions\Model\Settings\Error::ERROR_CODE_MANUAL_CUSTOMER);
            
			$this->messageManager->addSuccess(__('Subscription successfully suspended'));
        } catch(\Exception $e) {
			$this->messageManager->addError($e->getMessage());
        }
		
		return $resultRedirect->setPath('*/*/view', array('id' => $model->getId()));
    }
	
}
