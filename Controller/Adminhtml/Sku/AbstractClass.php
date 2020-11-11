<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Sku;

abstract class AbstractClass extends \Magento\Backend\App\Action {
	
    protected $_coreRegistry;
	
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
		parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_translateInline = $translateInline;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultRawFactory = $resultRawFactory;
    }
	
    /**
     * @return $this
     */
    protected function _initAction() {
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
	
    /**
     * @return bool
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_management');
    }
	
    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage) {
        $resultPage->setActiveMenu('Toppik_Subscriptions::subscriptions_management_sku')
            ->addBreadcrumb(__('Subscriptions'), __('Subscriptions'))
            ->addBreadcrumb(__('Management'), __('Management'))
            ->addBreadcrumb(__('Sku Relations'), __('Sku Relations'));
		
        return $resultPage;
    }
	
}
