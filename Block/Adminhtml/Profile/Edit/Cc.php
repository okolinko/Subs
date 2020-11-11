<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\Edit;

class Cc extends \Magento\Backend\Block\Widget\Form\Container {
    
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;
    
    /**
     * View constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->authorization = $authorization;
        parent::__construct($context, $data);
    }
    
    protected function _construct() {
        $this->_blockGroup  = 'Toppik_Subscriptions';
        $this->_controller  = 'adminhtml_profile_edit_cc';
        $this->_objectId    = 'edit_subscription';
        
        $this->buttonList->update('save', 'label', __('Change Cc'));
        $this->buttonList->remove('reset');
        $this->buttonList->remove('add');
        
        if($this->getProfile()->getCustomerId()) {
            $url = $this->getUrl(
                'toppikvault/customer/card',
                [
                    'profile_id'    => $this->getProfile()->getId(),
                    'customer_id'   => $this->getProfile()->getCustomerId()
                ]
            );
            
            $this->buttonList->add(
                'add_card_button',
                [
                        'label' => __('Add New Card'),
                        'onclick' => "window.location.href = '{$url}'; return false;",
                        'class' => 'task'
                ]
            );
        }
        
        parent::_construct();
    }
    
    /**
     * @return Profile
     */
    public function getProfile() {
        return $this->registry->registry('profile');
    }
    
    /**
     * @return mixed
     */
    public function getHeaderText() {
		return __('Edit Subscription # %1', $this->getProfile()->getId());
    }
    
    public function getSaveUrl() {
		return $this->getUrl('*/*/updatePost');
    }
	
    public function getBackUrl() {
        return $this->getUrl('*/*/view', ['profile_id' => $this->getProfile()->getId()]);
    }
	
}
