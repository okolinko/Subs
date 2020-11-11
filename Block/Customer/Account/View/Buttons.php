<?php
namespace Toppik\Subscriptions\Block\Customer\Account\View;

class Buttons extends \Magento\Framework\View\Element\Template {
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var FormKey
     */
    protected $formKey;
    
    /**
     * @var Data
     */
    protected $_subscriptionHelper;
	
    /**
     * @var \Toppik\Subscriptions\Model\Settings\ReasonFactory
     */
    protected $_reasonFactory;
	
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Form\FormKey $formKey,
		\Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        \Toppik\Subscriptions\Model\Settings\ReasonFactory $reasonFactory,
        array $data = []
    ) {
		$this->registry = $registry;
        $this->formKey = $formKey;
		$this->_subscriptionHelper = $subscriptionHelper;
        $this->_reasonFactory = $reasonFactory;
        parent::__construct($context, $data);
    }
    
    public function getProfile() {
		return $this->registry->registry('current_profile');
    }
    
    public function getConfirmationMessage() {
        return __('Are you sure you want to do this?');
    }
    
    public function getCancelUrl() {
        return $this->getUrl('*/*/cancel', array('id' => $this->getProfile()->getId()));
    }
    
    public function getSuspendUrl() {
        return $this->getUrl('*/*/suspend', array('id' => $this->getProfile()->getId()));
    }
    
    public function getActivateUrl() {
        return $this->getUrl('*/*/activate', array('id' => $this->getProfile()->getId()));
    }
    
    public function getUpdateUrl() {
        return $this->getUrl('*/*/update', array('id' => $this->getProfile()->getId()));
    }
    
    public function getFormKey() {
        return $this->formKey->getFormKey();
    }
    
    public function getIsCancelMode() {
        return $this->_subscriptionHelper->getIsCancelMode();
    }
    
    public function getReasons() {
        return $this->_reasonFactory->create()->toOptionArray();
    }
    
}
