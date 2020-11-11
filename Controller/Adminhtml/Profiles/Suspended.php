<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;

class Suspended extends \Magento\Backend\App\Action {
    
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Toppik_Subscriptions::subscriptions_profiles_suspended');
        $resultPage->addBreadcrumb(__('Subscriptions'), __('Subscriptions'));
        $resultPage->addBreadcrumb(__('Profiles'), __('Profiles'));
        $resultPage->addBreadcrumb(__('Suspended'), __('Suspended'));
        $resultPage->getConfig()->getTitle()->prepend(__('Suspended Profiles'));
        
        return $resultPage;
    }
    
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_profiles');
    }
    
}
