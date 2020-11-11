<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\Edit\Frequency\Edit;

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
        $this->getProfile()->setActionType('frequency');
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
            'unit_id',
            'select',
            [
                'label' => __('Frequency'),
                'title' => __('Frequency'),
                'name' => 'unit_id',
                'required' => true,
                'options' => $this->getSubscriptionItems()
            ]
        );
        
        $form->setValues($this->getProfile()->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        
        return parent::_prepareForm();
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
        $items = array();
		$subscription = $this->subscriptionHelper->getSubscriptionByProduct($this->getProfile()->getSubscriptionProduct());
        
        if($subscription) {
            foreach($subscription->getItemsCollection() as $_item) {
                $items[$_item->getId()] = __($_item->getPeriod()->getTitle());
                
                if($this->getProfile()->getFrequencyLength() == ($_item->getPeriod()->getLength() * $_item->getUnit()->getLength())) {
                    $this->getProfile()->setData('unit_id', $_item->getId());
                }
            }
        }
        
        return $items;
	}
    
}
