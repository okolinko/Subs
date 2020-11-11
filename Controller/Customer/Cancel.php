<?php
namespace Toppik\Subscriptions\Controller\Customer;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Cancel extends Action\Action {
    
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
            $reason_id  = (int) trim($this->getRequest()->getParam('message'));
			$model 		= $this->_objectManager->create('Toppik\Subscriptions\Model\Profile')->load($id);
			$session 	= $this->_objectManager->get('Magento\Customer\Model\Session');
            $helper     = $this->_objectManager->get('Toppik\Subscriptions\Helper\Report');
			$message    = '';
            
			if(!$model->getId()) {
				$this->messageManager->addError(__('This profile no longer exists.'));
				return $resultRedirect->setPath('subscriptions/customer');
			}
			
			if((int) $model->getCustomerId() !== (int) $session->getCustomerId()) {
				$this->messageManager->addError(__('This profile no longer exists.'));
				return $resultRedirect->setPath('subscriptions/customer');
			}
			
			if($model->canCancelAsCurrentUser() !== true) {
				$this->messageManager->addError(__('Unable cancel subscription'));
				return $resultRedirect->setPath('subscriptions/customer/view', ['id' => $model->getId()]);
			}
            
            foreach($this->_objectManager->get('Toppik\Subscriptions\Model\Settings\Reason')->getAllOptions() as $_option) {
                if(isset($_option['value']) && isset($_option['label'])) {
                    if((int) $_option['value'] === $reason_id) {
                        $message = $_option['label'];
                        break;
                    }
                }
            }
            
            if(empty($message)) {
                $this->messageManager->addError(__('Can\'t cancel profile without reason. Please specify reason.'));
                return $resultRedirect->setPath('subscriptions/customer/view', ['id' => $model->getId()]);
            }
            
            $model->changeStatusToCancel(__('Status changed by customer'), $message);
            
            $this->_objectManager->create('Toppik\Subscriptions\Model\Profile\Cancelled')
                ->setData('profile_id', $model->getId())
                ->setData('option_id', $reason_id)
                ->setData('ip', (isset($_SERVER['REMOTE_ADDR']) ? ip2long($_SERVER['REMOTE_ADDR']) : null))
                ->setData('message', $message)
                ->save();
            
            try {
                $helper->sendEmail(
                    $helper->getSaveCancelEmailTemplate(),
                    $model->getCustomer()->getEmail(),
                    $model->getStoreId(),
                    array(
                        'profile'   => $model,
                        'customer'  => $model->getCustomer(),
                        'next_date' => date('m/d/Y', strtotime($model->getNextOrderAt()))
                    )
                );
            } catch(\Exception $e) {
                $helper->log(
                        sprintf(
                            '%s %s', str_repeat('=', 5),
                            sprintf('CANNOT send email on subscription_cancel for subscription ID %s: %s', $model->getId(), $e->getMessage())
                        ),
                        [],
                        \Toppik\Subscriptions\Logger\Logger::ERROR
                    );
            }
            
			$this->messageManager->addSuccess(__('Subscription successfully cancelled'));
        } catch(\Exception $e) {
			$this->messageManager->addError($e->getMessage());
        }
		
		return $resultRedirect->setPath('subscriptions/customer/view', ['id' => $model->getId()]);
    }
	
}
