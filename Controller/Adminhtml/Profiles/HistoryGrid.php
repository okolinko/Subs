<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;

class HistoryGrid extends \Magento\Backend\App\Action {
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    
    /**
     * HistoryGrid constructor.
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
        $profile_id = (int) $this->getRequest()->getParam('profile_id');
        $model      = $this->_objectManager->create('Toppik\Subscriptions\Model\Profile');
        
        if($profile_id) {
            $model->load($profile_id);
        }
        
        if($model->getId()) {
            $this->registry->register('profile', $model);
            
            $this->getResponse()->setBody(
                $this->_view->getLayout()->createBlock(
                    'Toppik\Subscriptions\Block\Adminhtml\Profile\View\History'
                )
                ->toHtml()
            );
        }
    }
	
}
