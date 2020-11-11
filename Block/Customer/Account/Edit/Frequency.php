<?php
namespace Toppik\Subscriptions\Block\Customer\Account\Edit;

class Frequency extends \Magento\Framework\View\Element\Template {
    
    /**
     * @var string
     */
    protected $_template = 'customer/account/edit/frequency.phtml';
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * @var Data
     */
    protected $subscriptionHelper;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\Registry $registry,
		\Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
		$this->registry = $registry;
		$this->subscriptionHelper = $subscriptionHelper;
        parent::__construct($context, $data);
    }
    
    /**
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->pageConfig->getTitle()->set($this->getTitle());
    }
    
    public function getProfile() {
		return $this->registry->registry('current_profile');
    }
    
    public function getTitle() {
		return __('Edit Subscription # %1', $this->getProfile()->getId());
    }
    
    /**
     * @return string
     */
    public function getBillingPeriod() {
        $text = $this->getProfile()->getFrequencyTitle() . ' cycle. ';
        
        if($this->getProfile()->getIsInfinite() == \Toppik\Subscriptions\Model\Settings\Period::INFINITE) {
            $text .= 'Repeat until suspended or cancelled.';
        } else {
            $text .= 'Repeat ' . $this->getProfile()->getNumberOfOccurrences() . ' time(s).';
        }
        
        return $text;
    }
    
	public function getSubscriptionItems() {
		$subscription = $this->subscriptionHelper->getSubscriptionByProduct($this->getProfile()->getSubscriptionProduct());
		return $subscription ? $subscription->getItemsCollection() : array();
	}
    
}
