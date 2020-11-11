<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Points;

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
        $model = $this->_coreRegistry->registry('points_item');
        
        $this->_blockGroup  = 'Toppik_Subscriptions';
        $this->_controller  = 'adminhtml_points';
        $this->_headerText  = $model->getId() ? __('Edit item ID %1', $model->getId()) : __('Add New Item');
        $this->_objectId    = 'points';
        
        $this->buttonList->update('save', 'label', __('Save Item'));
        $this->buttonList->remove('add');
        
        parent::_construct();
    }
    
    /**
     * @return mixed
     */
    public function getHeaderText() {
        $model = $this->_coreRegistry->registry('points_item');
        return $model->getId() ? __('Edit item ID %1', $model->getId()) : __('Add New Item');
    }
    
}
