<?php
namespace Toppik\Subscriptions\Block\Adminhtml\Profile;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
	
    /**
     * Subscription grid collection
     *
     * @var \Toppik\Subscriptions\Model\ResourceModel\Profile\Grid\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Subscription model
     *
     * @var \Toppik\Subscriptions\Model\Profile
     */
    protected $_profileFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Toppik\Subscriptions\Model\ResourceModel\Profile\Grid\CollectionFactory $collectionFactory
     * @param \Toppik\Subscriptions\Model\Profile $profileFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Toppik\Subscriptions\Model\ResourceModel\Profile\Grid\CollectionFactory $collectionFactory,
        \Toppik\Subscriptions\Model\Profile $profileFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_profileFactory = $profileFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize grid
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('subscription_profilesGrid');
        $this->setDefaultSort('profile_id');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepare related item collection
     *
     * @return \Toppik\Subscriptions\Block\Adminhtml\Customer\Edit\Tab\Grid
     */
    protected function _prepareCollection()
    {
        $this->_beforePrepareCollection();
        return parent::_prepareCollection();
    }

    /**
     * Configuring and setting collection
     *
     * @return $this
     */
    protected function _beforePrepareCollection()
    {
        if (!$this->getCollection()) {
            $collection = $this->_collectionFactory->create();
            $this->setCollection($collection);
        }
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return \Toppik\Subscriptions\Block\Adminhtml\Customer\Edit\Tab\Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'profile_id',
            [
                'header' => __('ID'),
                'index' => 'profile_id',
                'type' => 'number'
            ]
        );
		
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'type' => 'text'
            ]
        );
		
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_profileFactory->getAvailableStatuses(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );
		
        $this->addColumn(
            'grand_total',
            [
                'header' => __('Amount'),
                'index' => 'grand_total',
                'type' => 'text'
            ]
        );
		
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'type' => 'datetime',
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );

        $this->addColumn(
            'start_date',
            [
                'header' => __('Start Date'),
                'index' => 'start_date',
                'type' => 'date',
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );
		
        $this->addColumn(
            'last_order_id',
            [
                'header' => __('Last Order'),
                'index' => 'last_order_id',
                'type' => 'number'
            ]
        );
		
        $this->addColumn(
            'last_order_at',
            [
                'header' => __('Last Order Date'),
                'index' => 'last_order_at',
                'type' => 'datetime',
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => ['base' => $this->_getControllerUrl('view')],
                        'field' => 'profile_id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
                'header_css_class' => 'col-actions',
                'column_css_class' => 'col-actions'
            ]
        );

        return parent::_prepareColumns();
    }
	
    /**
     * Get Url to action
     *
     * @param  string $action action Url part
     * @return string
     */
    protected function _getControllerUrl($action = '')
    {
        return 'subscriptions/profiles/' . $action;
    }

    /**
     * Retrieve row url
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('subscriptions/profiles/view', ['profile_id' => $row->getId()]);
    }
}
