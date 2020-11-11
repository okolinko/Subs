<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\Edit\Cc\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic {
    
    /**
     * @var \Toppik\Subscriptions\Helper\Data
     */
    protected $subscriptionHelper;
    
    /**
     * @var \Magento\Vault\Model\PaymentTokenManagement
     */
    private $paymentTokenManagement;
    
    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\FormFactory $formFactory,
		\Toppik\Subscriptions\Helper\Data $subscriptionHelper,
        \Magento\Vault\Model\PaymentTokenManagement $paymentTokenManagement,
        array $data
    ) {
		$this->subscriptionHelper = $subscriptionHelper;
        $this->paymentTokenManagement = $paymentTokenManagement;
        parent::__construct($context, $coreRegistry, $formFactory, $data);
    }
    
    protected function _construct() {
        parent::_construct();
        $this->setId('edit_subscription');
        $this->setTitle(__('Edit Subscription'));
        $this->getProfile()->setActionType('cc');
    }
    
    /**
     * @return Profile
     */
    public function getProfile() {
        return $this->_coreRegistry->registry('profile');
    }
    
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm() {
        /* @var FormClass $form */
        $form = $this->_formFactory->create([
            'data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']
        ]);
        
        $form->setHtmlIdPrefix('');
        
        $generalFieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('Choose An Option'), 'class' => 'fieldset-wide']
        );
        
        $generalFieldset->addField('profile_id', 'hidden', ['name' => 'profile_id']);
        $generalFieldset->addField('action_type', 'hidden', ['name' => 'action_type']);
        
        $generalFieldset->addField(
            'gateway_token',
            'select',
            [
                'label' => __('Card'),
                'title' => __('Card'),
                'name' => 'gateway_token',
                'required' => true,
                'options' => $this->getItems()
            ]
        );
        
        $form->setValues($this->getProfile()->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
	public function getItems() {
        $items = array();
        
        if($this->getProfile()->getCustomerId()) {
            $collection = $this->paymentTokenManagement->getVisibleAvailableTokens($this->getProfile()->getCustomerId());
            
            if(is_array($collection) && count($collection) > 0) {
                foreach($collection as $_token) {
                    if($this->getProfile()->getPaymentTokenId() === $_token->getId()) {
                        $this->getProfile()->setGatewayToken($_token->getGatewayToken());
                    }
                    
                    $details = \Zend\Json\Json::decode($_token->getDetails());
                    $items[$_token->getGatewayToken()] = __('Card %1 %2 (%3)', $details->type, str_repeat('*', 12) . $details->maskedCC, $details->expirationDate);
                }
            }
        }
        
        return $items;
	}
    
}
