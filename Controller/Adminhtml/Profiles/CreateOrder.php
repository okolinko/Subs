<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 8/29/16
 * Time: 4:07 PM
 */

namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;


use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Toppik\Subscriptions\Converter\ProfileToOrder;
use Toppik\Subscriptions\Model\ResourceModel\Profile as ProfileResourceModel;

class CreateOrder extends Action {

    /**
     * @var ProfileResourceModel
     */
    private $profileResourceModel;

    /**
     * CreateOrder constructor.
     * @param ProfileResourceModel $profileResourceModel
     * @param Action\Context $context
     */
    public function __construct(
        ProfileResourceModel $profileResourceModel,
        Action\Context $context
    )
    {
        $this->profileResourceModel = $profileResourceModel;
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
        $id = $this->getRequest()->getParam('profile_id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if($id) {
            try {
                /* @var \Toppik\Subscriptions\Model\Profile $profile */
                $profile = $this->_objectManager->create('Toppik\Subscriptions\Model\Profile');
                $profile->load($id);
                /* @var ProfileToOrder $profileToOrder */
                $profileToOrder = $this->_objectManager->create('Toppik\Subscriptions\Converter\ProfileToOrder');
                $order = $profileToOrder->process($profile, []);
                $profile->setLastOrderId($order->getId());
                $profile->setLastOrderAt($order->getCreatedAt());
                $profile->scheduleNextOrder();
                $profile->save();
                $this->messageManager->addSuccess(__('The order has been created.'));
            } catch(LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch(\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while creating the order.'));
            }
        }
        return $resultRedirect->setPath('*/*/all');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Toppik_Subscriptions::subscriptions_profiles');
    }
}