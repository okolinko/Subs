<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;

class MassActivate extends \Magento\Backend\App\Action {
    
    /**
     * @var Filter
     */
    protected $filter;
    
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Toppik\Subscriptions\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
    ) {
		parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }
    
    public function execute() {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        
        foreach($collection as $_model) {
            try {
                if(!$_model->canActivate()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Unable to activate profile ID %1', $_model->getId()));
                }
                
				$_model->changeStatusToActive(__('Status changed by admin'));
                $this->messageManager->addSuccess(__('Profile ID %1 has been activated.', $_model->getId()));
            } catch(\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch(\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while activating profile ID %1: %2', $_model->getId(), $e->getMessage()));
            }
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();
        return $resultRedirect;
    }
	
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_profiles_activate');
    }
	
}
