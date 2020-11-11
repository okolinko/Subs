<?php
namespace Toppik\Subscriptions\Block\Customer\Account\View;

class Billing extends \Magento\Framework\View\Element\Template {
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;
    
    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        array $data = []
    ) {
		$this->registry = $registry;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        parent::__construct($context, $data);
    }
    
    public function getProfile() {
		return $this->registry->registry('current_profile');
    }
    
    public function getInfoBoxTitle() {
        return __('Billing Address');
    }
    
    public function getAddress() {
        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray($this->getProfile()->getBillingAddress()->getData());
    }
    
    public function canEditAddress() {
        return $this->getProfile()->canEditBillingAddress();
    }
    
    public function getEditAddressLabel() {
		return __('Edit Billing Address');
    }
    
    public function getEditAddressUrl() {
        $profile    = $this->getProfile();
        $token      = $profile->getPaymentTokenReference();
        
        return $this->getUrl('toppikvault/customer/view', array('profile_id' => $this->getProfile()->getId(), 'type' => 'billing', 'h' => $token->getPublicHash()));
    }
    
}
