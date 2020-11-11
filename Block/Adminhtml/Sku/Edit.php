<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Sku;

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
        $this->_blockGroup  = 'Toppik_Subscriptions';
        $this->_controller  = 'adminhtml_sku';
        $this->_headerText  = __('Sap Order Queue');
        $this->_objectId    = 'sku_item';
        
        $model = $this->_coreRegistry->registry('item');
        
        $this->buttonList->update('save', 'label', __('Save Item'));
        $this->buttonList->remove('add');
        
        if($model->getId()) {
            $message 	= 'Are you sure you want to do this?';
            $url 		= $this->getUrl('subscriptions/sku/delete', ['id' => $model->getId()]);
            
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
        $model = $this->_coreRegistry->registry('item');
        return $model->getId() ? __('Edit item # %1', $model->getId()) : __('Add New Item');
    }
    
}
