<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 4:18 PM
 */

namespace Toppik\Subscriptions\Controller\Adminhtml\Periods;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Ui\Component\MassAction\Filter;
use Toppik\Subscriptions\Model\ResourceModel\Settings\Period\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Toppik\Subscriptions\Model\Settings\Period;

class MassHide extends Action {

    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        Context $context)
    {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {

            /** @var Period $item */
            $item
                ->setIsVisible(Period::HIDDEN)
                ->save();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been hided.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_settings_periods');
    }
}