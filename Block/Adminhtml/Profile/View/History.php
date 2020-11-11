<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile\View;

class History extends \Magento\Backend\Block\Widget\Grid\Extended {
    
    /**
     * @var \Toppik\Subscriptions\Model\ResourceModel\Profile\History\Collection
     */
    private $collectionFactory;
    
    /**
     * @var Registry
     */
    private $registry;
    
    /**
     * History constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Toppik\Subscriptions\Model\ResourceModel\Profile\History\Collection $collectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Toppik\Subscriptions\Model\ResourceModel\Profile\History\CollectionFactory $collectionFactory,
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
        $this->setId('profile_status_history_grid');
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
            'created_at',
            [
                'header' => __('Date'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );
        
        $this->addColumn(
            'action_code',
            [
                'header' => __('Action Code'),
                'index' => 'action_code',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'customer_id',
            [
                'header' => __('Customer ID'),
                'index' => 'customer_id',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'customer_email',
            [
                'header' => __('Customer Email'),
                'index' => 'customer_email',
                'type' => 'text'
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
            'ip_converted',
            [
                'header' => __('IP'),
                'index' => 'ip_converted',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'cc',
            [
                'header' => __('Payment Token ID'),
                'index' => 'cc',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'qty',
            [
                'header' => __('QTY'),
                'index' => 'qty',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'frequency',
            [
                'header' => __('Frequency'),
                'index' => 'frequency',
                'type' => 'text'
            ]
        );
        
        $this->addColumn(
            'next_order_at',
            [
                'header' => __('Next Order At'),
                'index' => 'next_order_at',
                'type' => 'datetime'
            ]
        );
        
        $this->addColumn(
            'message',
            [
                'header' => __('System Message'),
                'index' => 'message'
            ]
        );
        
        $this->addColumn(
            'note',
            [
                'header' => __('Reason'),
                'index' => 'note'
            ]
        );
        
        $this->addColumn(
            'last_suspend_error',
            [
                'header' => __('Last Suspend Error'),
                'index' => 'last_suspend_error'
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
        return $this->getUrl('*/*/historyGrid', ['_current' => true]);
    }
    
}
