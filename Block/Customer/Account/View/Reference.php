<?php
namespace Toppik\Subscriptions\Block\Customer\Account\View;

class Reference extends \Magento\Framework\View\Element\Template {
    
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
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
     * Retrieve current profile model instance
     *
     * @return AW_Sarp2_Model_Profile
     */
    public function getProfile() {
		return $this->registry->registry('current_profile');
    }
    
    public function getInfoBoxTitle() {
        return __('Reference');
    }
    
    public function getInfoBoxFields() {
        $profile    = $this->getProfile();
        $token      = $profile->getPaymentTokenReference();
        $details    = $token->getDetails() ? \Zend\Json\Json::decode($token->getDetails()) : '';
        
		$fields = array(
            array(
                'title' => __('Payment Method:'),
                'value' => ($details ? (($details->type ? __('Card %1 %2 %3', $details->type, str_repeat('*', 4) . $details->maskedCC, $details->expirationDate) : __('Credit Card')) . ($profile->canEditCc() ? ' <a href="' . $this->getUrl('subscriptions/customer/cc', array('id' => $profile->getId())).'"><u>(' . __('edit') . ')</u></a>' : '')) : '')
            ),
            
            array(
                'title' => __('Payment Reference ID:'),
                'value' => $profile->getPaymentTokenReference()->getData('gateway_token'),
                'hidden' => true
            ),
            
            array(
                'title' => __('Schedule Description:'),
                'value' => __('Recurring profile for product: %1', $this->getProfileName($profile))
            ),
            
            array(
                'title' => __('Profile State:'),
                'value' => $this->getProfileStatus($profile)
            )
		);
		
		return $fields;
    }
    
    /**
     * @return string
     */
    public function getProfileName($profile) {
        $value = array();
        
        foreach($profile->getAllVisibleItems() as $_item) {
            if((int) $_item->getIsOnetimeGift() !== 1) {
                $value[] = $_item->getName();
            }
        }
        
        return implode(', ', $value);
    }
    
    /**
     * @return Phrase
     */
    public function getProfileStatus($profile) {
        $availableStatuses = $profile->getAvailableStatuses();
        $status = $profile->getStatus();
        
        if(isset($availableStatuses[$status])) {
            return $availableStatuses[$status];
        } else {
            return __('Unknown');
        }
    }
    
}
