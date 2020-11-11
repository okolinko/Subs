<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Points\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic {
    
    /**
     * @var
     */
    protected $systemStore;
    
    /**
     * Form constructor.
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data
    ) {
        $this->systemStore = $systemStore;
        parent::__construct($context, $coreRegistry, $formFactory, $data);
    }
    
    protected function _construct() {
        parent::_construct();
        $this->setId('order_queue_form');
        $this->setTitle(__('Item Information'));
    }
    
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm() {
        $model = $this->_coreRegistry->registry('points_item');
        
        /* @var FormClass $form */
        $form = $this->_formFactory->create([
            'data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']
        ]);
        
        $form->setHtmlIdPrefix('');
        
        $generalFieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
        
        $generalFieldset->addField('id', 'hidden', ['name' => 'id']);
        
        $generalFieldset->addField(
            'title',
            'text',
            [
                'label'     => __('Title'),
                'title'     => __('Title'),
                'name'      => 'title',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'description',
            'textarea',
            [
                'label'     => __('Description'),
                'title'     => __('Description'),
                'name'      => 'description',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'points',
            'text',
            [
                'label'     => __('Points'),
                'title'     => __('Points'),
                'name'      => 'points',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'position',
            'text',
            [
                'label'     => __('Position'),
                'title'     => __('Position'),
                'name'      => 'position',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'manager',
            'select',
            [
                'label'     => __('Manager'),
                'title'     => __('Manager'),
                'name'      => 'manager',
                'required'  => true,
                'options'   => $model->getAvailableManager()
            ]
        );
        
        $generalFieldset->addField(
            'type_id',
            'select',
            [
                'label'     => __('Type'),
                'title'     => __('Type'),
                'name'      => 'type_id',
                'required'  => true,
                'options'   => $model->getAvailableTypes()
            ]
        );
        
        $generalFieldset->addField(
            'rule_id',
            'text',
            [
                'label'     => __('Rule ID'),
                'title'     => __('Rule ID'),
                'name'      => 'rule_id',
                'required'  => true
            ]
        );
        
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
}
