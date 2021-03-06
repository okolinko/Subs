<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/26/16
 * Time: 5:18 PM
 */

namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;

class All extends Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Toppik_Subscriptions::subscriptions_profiles_all');
        $resultPage->addBreadcrumb(__('Subscriptions'), __('Subscriptions'));
        $resultPage->addBreadcrumb(__('Profiles'), __('Profiles'));
        $resultPage->addBreadcrumb(__('All'), __('All'));
        $resultPage->getConfig()->getTitle()->prepend(__('All Profiles'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_profiles');
    }

}