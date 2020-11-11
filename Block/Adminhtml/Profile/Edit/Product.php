<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\Edit;

class Product extends \Magento\Backend\Block\Widget\Container {
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }
    
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->setId('profile_edit_product');
        
        $url = $this->getUrl(
            'subscriptions/profiles/view',
            [
                'profile_id' => $this->getProfile()->getId()
            ]
        );
        
        $this->buttonList->add(
            'cancel',
            [
                'label' => __('Cancel'),
                'class' => 'button',
                'onclick' => "location.href = '{$url}'"
            ]
        );
        
        $this->buttonList->add(
            'save',
            [
                'label' => __('Save'),
                'class' => 'button primary js-subscription_product_save'
            ]
        );
        
    }
    
    /**
     * @return Profile
     */
    public function getProfile() {
        return $this->_registry->registry('profile');
    }
    
    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText() {
        return __('Please select product');
    }
    
}
