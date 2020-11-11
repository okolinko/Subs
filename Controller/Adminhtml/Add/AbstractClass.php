<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Add;

abstract class AbstractClass extends \Magento\Backend\App\Action {

    protected $_coreRegistry;

    protected $_productRepository;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $_ruleFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
		parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_ruleFactory = $ruleFactory;
        $this->_fileFactory = $fileFactory;
        $this->_translateInline = $translateInline;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->_productRepository = $productRepository;
    }

    /**
     * @return bool
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_management_add');
    }

    /**
     * @return $this
     */
    protected function _initAction() {
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Toppik_Subscriptions::subscriptions_management_add');
        $resultPage->addBreadcrumb(__('Subscriptions'), __('Subscriptions'));
        $resultPage->addBreadcrumb(__('Management'), __('Management'));
        $resultPage->addBreadcrumb(__('Add Products'), __('Add Products'));
        $resultPage->getConfig()->getTitle()->prepend(__('Add Products'));

        return $resultPage;
    }

}
