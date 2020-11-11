<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\Edit\Nextdate\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic {
    
    /**
     * @var \Toppik\Subscriptions\Helper\Data
     */
    protected $subscriptionHelper;
    
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
        array $data
    ) {
		$this->subscriptionHelper = $subscriptionHelper;
        parent::__construct($context, $coreRegistry, $formFactory, $data);
    }
    
    protected function _construct() {
        parent::_construct();
        $this->setId('edit_subscription');
        $this->setTitle(__('Edit Subscription'));
        $this->getProfile()->setActionType('next_order_date');
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
            ['legend' => __('Please specify reason of this update'), 'class' => 'fieldset-wide']
        );
        
        $generalFieldset->addField('profile_id', 'hidden', ['name' => 'profile_id']);
        $generalFieldset->addField('action_type', 'hidden', ['name' => 'action_type']);
        
        $generalFieldset->addField(
            'date',
            'date',
            [
                'label' => __('Next Order Date'),
                'title' => __('Next Order Date'),
                'name' => 'date',
                'format' => 'Y-m-d',
                'required' => true
            ]
        );
        
        $generalFieldset->addField(
            'note',
            'textarea',
            [
                'label' => __('Reason'),
                'title' => __('Reason'),
                'name' => 'note',
                'required' => true
            ]
        );
        
        $form->setValues($this->getProfile()->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
}
