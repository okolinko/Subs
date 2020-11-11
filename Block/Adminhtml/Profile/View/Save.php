<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\View;

class Save extends \Magento\Backend\Block\Widget\Grid\Extended {
    
    /**
     * @var \Toppik\Subscriptions\Model\ResourceModel\Profile\Save\Collection
     */
    private $collectionFactory;
    
    /**
     * @var Registry
     */
    private $registry;
    
    /**
     * Save constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Toppik\Subscriptions\Model\ResourceModel\Profile\Save\Collection $collectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Toppik\Subscriptions\Model\ResourceModel\Profile\Save\CollectionFactory $collectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    
    protected function _construct() {
        parent::_construct();
        $this->setId('profile_save_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
        $this->setSortable(true);
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
        $this->setUseAjax(true);
    }
    
    protected function _prepareCollection() {
        /* @var \Toppik\Subscriptions\Model\Profile $profile */
        $profile = $this->registry->registry('profile');
        
        $collection = $this->collectionFactory->create();
        
        $collection->setProfileFilter($profile->getId());
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns() {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'index' => 'id'
            ]
        );
        
        $this->addColumn(
            'created_at',
            [
                'header' => __('Date'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );
        
        $this->addColumn(
            'admin_id',
            [
                'header' => __('Admin ID'),
                'index' => 'admin_id',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'admin_email',
            [
                'header' => __('Admin Email'),
                'index' => 'admin_email',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'option_id',
            [
                'header' => __('Option ID'),
                'index' => 'option_id',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'option_title',
            [
                'header' => __('Option Title'),
                'index' => 'option_title',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'option_points',
            [
                'header' => __('Option Points'),
                'index' => 'option_points',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'value',
            [
                'header' => __('Option Value'),
                'index' => 'value',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'used_points',
            [
                'header' => __('Used Points'),
                'index' => 'used_points',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'subscription_points',
            [
                'header' => __('Subscription Points'),
                'index' => 'subscription_points',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'admin_points',
            [
                'header' => __('Admin Points'),
                'index' => 'admin_points',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'ip_converted',
            [
                'header' => __('IP'),
                'index' => 'ip_converted',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'message',
            [
                'header' => __('Message'),
                'index' => 'message'
            ]
        );
        
        return parent::_prepareColumns();
    }
    
    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/saveGrid', ['_current' => true]);
    }
    
}
