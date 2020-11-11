<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Toppik\Subscriptions\Model\Profile;

class Update extends Action {

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $profile_id = (int) $this->getRequest()->getParam('profile_id');
        $page 		= 'profile';
		
        $model = $this->_objectManager->create('Toppik\Subscriptions\Model\Profile')->load($profile_id);
		
		if(!$model->getId()) {
            $this->messageManager->addError(__('Profile with ID %1 does not exist.', $profile_id));
            $this->_redirect('customer/index/index');
            return;
		}
		
        $login = $this->_objectManager->create('\Magefan\LoginAsCustomer\Model\Login')->setCustomerId($model->getCustomerId());
		
        $login->deleteNotUsed();
		
        $customer = $login->getCustomer();
		
        if(!$customer->getId()) {
            $this->messageManager->addError(__('Customer with this ID are no longer exist.'));
            $this->_redirect('customer/index/index');
            return;
        }
		
        $user = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getUser();
        $login->generate($user->getId());
		
        $this->getResponse()->setRedirect(
			$this->_objectManager->get('Magento\Framework\Url')->setScope(
				$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore($customer->getStoreId())
			)
			->getUrl('loginascustomer/login/index', ['secret' => $login->getSecret(), '_nosid' => true, 'page' => $page, 'id' => $profile_id])
		);
    }
	
    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magefan_LoginAsCustomer::login_log');
    }
	
}
