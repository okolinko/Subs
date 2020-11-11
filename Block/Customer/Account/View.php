<?php
namespace Toppik\Subscriptions\Block\Customer\Account;

use Magento\Framework\ObjectManagerInterface;

class View extends \Magento\Framework\View\Element\Template
{
	
    /**
     * @var string
     */
    protected $_template = 'customer/account/view.phtml';
	
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
	
    /**
     * @var Data
     */
    protected $_subscriptionHelper;
	
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        array $data = []
    ) {
		$this->registry = $registry;
		$this->_subscriptionHelper = $subscriptionHelper;
        parent::__construct($context, $data);
    }
	
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set($this->getTitle());
    }
	
    public function getProfile()
    {
		return $this->registry->registry('current_profile');
    }
	
    public function getTitle()
    {
		$id = $this->getProfile()->getData('reference_id') ? $this->getProfile()->getData('reference_id') : $this->getProfile()->getId();
		return __('Subscription #%1', $id);
    }
	
    public function getReferenceHtml()
    {
        return $this->getChildHtml('reference');
    }
	
    public function getPurchaseHtml()
    {
        return $this->getChildHtml('purchase');
    }
	
    public function getScheduleHtml()
    {
        return $this->getChildHtml('schedule');
    }
	
    public function getPaymentsHtml()
    {
        return $this->getChildHtml('payments');
    }
	
    public function getBillingHtml()
    {
        return $this->getChildHtml('billing');
    }
	
    public function getShippingHtml()
    {
        return $this->getChildHtml('shipping');
    }
	
    public function getIsCancelMode() {
        return $this->_subscriptionHelper->getIsCancelMode();
    }
    
}
