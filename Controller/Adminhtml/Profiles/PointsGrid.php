<?php
namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;

class PointsGrid extends \Magento\Backend\App\Action {
    
    public function execute() {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Toppik\Subscriptions\Block\Adminhtml\Profile\View\Points'
            )
            ->toHtml()
        );
    }
	
}
