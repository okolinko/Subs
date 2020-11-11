<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Add;

class Edit extends \Magento\Backend\Block\Widget\Form\Container {
    
    protected $_coreRegistry;
    
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;
	
    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $data
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->authorization = $authorization;
        parent::__construct($context, $data);
    }
    
    protected function _construct() {
        $model = $this->_coreRegistry->registry('add_item');
        
        $this->_blockGroup  = 'Toppik_Subscriptions';
        $this->_controller  = 'adminhtml_add';
        $this->_headerText  = $model->getId() ? __('Edit item ID %1', $model->getId()) : __('Add New Item');
        $this->_objectId    = 'add';
        
        $this->buttonList->update('save', 'label', __('Save Item'));
        $this->buttonList->remove('add');
        
        if($model->getId()) {
            $message 	= 'Are you sure you want to do this?';
            $url 		= $this->getUrl('subscriptions/add/delete', ['id' => $model->getId()]);
            
            $this->buttonList->add(
                'delete',
                [
                    'label' => __('Delete'),
                    'onclick' => "confirmSetLocation('{$message}', '{$url}')"
                ]
            );
        }
        
        parent::_construct();
    }
    
    /**
     * @return mixed
     */
    public function getHeaderText() {
        $model = $this->_coreRegistry->registry('add_item');
        return $model->getId() ? __('Edit item ID %1', $model->getId()) : __('Add New Item');
    }
    
}
