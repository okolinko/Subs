<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;

class Product extends \Magento\Backend\App\Action {
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    
    /**
     * View constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        parent::__construct($context);
    }
    
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        
        try {
            $id     = (int) $this->getRequest()->getParam('profile_id');
            $model  = $this->_objectManager->create('Toppik\Subscriptions\Model\Profile');
            
            if($id) {
                $model->load($id);
            }
            
			if(!$model->getId()) {
                $this->messageManager->addError(__('This profile no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/all');
			}
            
			if($model->canUpdate() !== true) {
                $this->messageManager->addError(__('Can\'t edit product of current profile'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/all');
			}
            
            $this->registry->register('profile', $model);
        } catch(\Exception $e) {
			$this->messageManager->addError($e->getMessage());
            return $resultPage->setPath('*/*/all');
        }
        
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Subscription # %1', $model->getId()));
        
        return $resultPage;
    }
    
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_management');
    }
    
}
