<?php
namespace Toppik\Subscriptions\Block\Customer\Account\Edit;

class Quantity extends \Magento\Framework\View\Element\Template {
    
    /**
     * @var string
     */
    protected $_template = 'customer/account/edit/quantity.phtml';
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
		$this->registry = $registry;
        $this->objectManager = $objectManager;
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
    
}
