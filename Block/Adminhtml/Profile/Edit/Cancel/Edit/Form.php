<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\Edit\Cancel\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic {
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    /**
     * @var \Toppik\Subscriptions\Helper\Data
     */
    protected $subscriptionHelper;
    
    /**
     * @var \Toppik\Subscriptions\Helper\Report
     */
    private $reportHelper;
    
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
        \Magento\Framework\ObjectManagerInterface $objectManager,
		\Toppik\Subscriptions\Helper\Data $subscriptionHelper,
		\Toppik\Subscriptions\Helper\Report $reportHelper,
        array $data
    ) {
        $this->_objectManager = $objectManager;
		$this->subscriptionHelper = $subscriptionHelper;
		$this->reportHelper = $reportHelper;
        parent::__construct($context, $coreRegistry, $formFactory, $data);
    }
    
    protected function _construct() {
        parent::_construct();
        $this->setId('edit_subscription');
        $this->setTitle(__('Edit Subscription'));
        $this->getProfile()->setActionType('cancel');
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
        
        $form->setHtmlIdPrefix('field_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        
        $generalFieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('Are you sure you want to cancel subscription? If so, please choose reason for the cancellation'), 'class' => 'fieldset-wide']
        );
        
        $generalFieldset->addField('profile_id', 'hidden', ['name' => 'profile_id']);
        $generalFieldset->addField('action_type', 'hidden', ['name' => 'action_type']);
        
        $reasons = $this->_objectManager->get('Toppik\Subscriptions\Model\Settings\Reason');
        $points = $this->_objectManager->get('Toppik\Subscriptions\Model\Settings\Points');
        
        $generalFieldset->addField(
            'note_option',
            'select',
            [
                'label'     => __('Reason'),
                'title'     => __('Reason'),
                'name'      => 'note_option',
                'required'  => true,
                'values'    => $reasons->toOptionArray()
            ],
            'to'
        );
        
        $form->setValues($this->getProfile()->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
}
