<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\Edit\Shipping\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic {
    
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $country;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;
    
    /**
     * @var mixed
     */
    protected $_countryCollection;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $_regionCollection;
	
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
        \Magento\Directory\Model\Config\Source\Country $country,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        array $data
    ) {
        parent::__construct($context, $coreRegistry, $formFactory, $data);
        $this->country = $country;
        $this->regionFactory = $regionFactory;
    }
    
    protected function _construct() {
        parent::_construct();
        $this->setId('edit_subscription');
        $this->setTitle(__('Edit Subscription'));
        $this->getProfile()->getShippingAddress()->setActionType('shipping');
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
            ['legend' => __('Shipping Address'), 'class' => 'fieldset-wide']
        );
        
        $generalFieldset->addField('profile_id', 'hidden', ['name' => 'profile_id']);
        $generalFieldset->addField('action_type', 'hidden', ['name' => 'action_type']);
        
        $generalFieldset->addField(
            'firstname',
            'text',
            [
                'label'     => __('First Name'),
                'title'     => __('First Name'),
                'name'      => 'firstname',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'lastname',
            'text',
            [
                'label'     => __('Last Name'),
                'title'     => __('Last Name'),
                'name'      => 'lastname',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'street',
            'text',
            [
                'label'     => __('Street Address'),
                'title'     => __('Street Address'),
                'name'      => 'street[]',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'street_2',
            'text',
            [
                'label'     => __(''),
                'title'     => __(''),
                'name'      => 'street[]',
                'required'  => false
            ]
        );
        
        $generalFieldset->addField(
            'city',
            'text',
            [
                'label'     => __('City'),
                'title'     => __('City'),
                'name'      => 'city',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'postcode',
            'text',
            [
                'label'     => __('Zip Code'),
                'title'     => __('Zip Code'),
                'name'      => 'postcode',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'region_id',
            'select',
            [
                'label'     => __('State/Province'),
                'title'     => __('State/Province'),
                'name'      => 'region_id',
                'required'  => false,
                'values'    => []
            ]
        );
        
        $generalFieldset->addField(
            'region',
            'text',
            [
                'label'     => __('State/Province'),
                'title'     => __('State/Province'),
                'name'      => 'region',
                'required'  => false
            ]
        );
        
        $generalFieldset->addField(
            'country_id',
            'select',
            [
                'label'     => __('Country'),
                'title'     => __('Country'),
                'name'      => 'country_id',
                'required'  => true,
                'values'    => $this->_getCountryOptions()
            ]
        );
        
        $generalFieldset->addField(
            'telephone',
            'text',
            [
                'label'     => __('Phone'),
                'title'     => __('Phone'),
                'name'      => 'telephone',
                'required'  => true
            ]
        );
        
        $form->setValues($this->getProfile()->getShippingAddress()->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('Magento\Framework\View\Element\Template')->setTemplate('Toppik_Subscriptions::profile/shipping/scripts.phtml')
        );
        
        return parent::_prepareForm();
    }
    
    /**
     * Try to load country options from cache
     * If it is not exist load options from country collection and save to cache
     *
     * @return array
     */
    protected function _getRegionOptions() {
        $options = false;
        
        if($options == false) {
            $options = $this->regionFactory->create()->getCollection()->toOptionArray();
        }
        
        return $options;
    }
    
    /**
     * Try to load country options from cache
     * If it is not exist load options from country collection and save to cache
     *
     * @return array
     */
    protected function _getCountryOptions() {
        $options = false;
        
        if($options == false) {
            $options = $this->country->toOptionArray(false, 'US');
        }
        
        return $options;
    }
    
}
