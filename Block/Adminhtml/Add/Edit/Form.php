<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Add\Edit;

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
        $this->setId('subscriptions_add_form');
        $this->setTitle(__('Item Information'));
    }
    
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm() {
        $model = $this->_coreRegistry->registry('add_item');
        
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
            'sku',
            'text',
            [
                'label'     => __('Sku'),
                'title'     => __('Sku'),
                'name'      => 'sku',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'price',
            'text',
            [
                'label'     => __('Price'),
                'title'     => __('Price'),
                'name'      => 'price',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'qty',
            'text',
            [
                'label'     => __('Qty'),
                'title'     => __('Qty'),
                'name'      => 'qty',
                'required'  => true
            ]
        );
        
        $generalFieldset->addField(
            'status',
            'select',
            [
                'label'     => __('Status'),
                'title'     => __('Status'),
                'name'      => 'status',
                'required'  => true,
                'options'   => $model->getAvailableStatus()
            ]
        );
        
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
}
