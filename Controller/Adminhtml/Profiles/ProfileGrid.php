<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;

use Magento\Backend\App\Action;

class ProfileGrid extends Action
{
	
    /**
     * Generate profiles grid for ajax request from customer page
     *
     * @return void
     */
    public function execute()
    {
        $customerId = intval($this->getRequest()->getParam('id'));
		
        if ($customerId) {
            $this->getResponse()->setBody(
                $this->_view->getLayout()->createBlock(
                    'Toppik\Subscriptions\Block\Adminhtml\Customer\Edit\Tab\Profile'
                )->setCustomerId(
                    $customerId
                )->toHtml()
            );
        }
    }
	
}
