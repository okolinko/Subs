<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 10/3/16
 * Time: 5:51 PM
 */

namespace Toppik\Subscriptions\Block\Adminhtml\Profile\View;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Registry;
use Magento\Sales\Model\Config\Source\Order\Status as OrderStatusSource;
use Magento\Sales\Model\Order\Config as OrderConfig;

class OrderGrid extends Extended {
    
    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;
    
    /**
     * @var Registry
     */
    private $registry;
    
    /**
     * @var OrderStatusSource
     */
    private $orderStatusSource;
    
    /**
     * @var OrderConfig
     */
    private $orderConfig;
    
    /**
     * OrderGrid constructor.
     * @param OrderConfig $orderConfig
     * @param OrderStatusSource $orderStatusSource
     * @param Registry $registry
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Context $context
     * @param Data $backendHelper
     * @param array $data
     */
    public function __construct(
        OrderConfig $orderConfig,
        OrderStatusSource $orderStatusSource,
        Registry $registry,
        OrderCollectionFactory $orderCollectionFactory,
        Context $context,
        Data $backendHelper,
        array $data = []
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->registry = $registry;
        $this->orderStatusSource = $orderStatusSource;
        $this->orderConfig = $orderConfig;
        parent::__construct($context, $backendHelper, $data);
    }
    
    protected function _construct() {
        parent::_construct();
        $this->setId('profile_order_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setSortable(true);
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
        $this->setUseAjax(true);
    }
    
    protected function _prepareCollection() {
        /* @var Collection $collection */
        $collection = $this->orderCollectionFactory->create();
        /* @var \Toppik\Subscriptions\Model\Profile $profile */
        $profile = $this->registry->registry('profile');
        $collection->join(
            'subscriptions_profiles_orders',
            'main_table.entity_id = subscriptions_profiles_orders.order_id'
        );
        $collection->getSelect()
            ->group('main_table.entity_id')
            ->where('subscriptions_profiles_orders.profile_id = ?', $profile->getId());
        $collection->addAddressFields();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns() {
        $this->addColumn(
            'increment_id',
            [
                'header' => __('Order ID'),
                'index' => 'increment_id',
                'type' => 'number',
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Purchased On'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );
        $this->addColumn(
            'updated_at',
            [
                'header' => __('Updated At'),
                'index' => 'updated_at',
                'type' => 'datetime',
            ]
        );
        $this->addColumn(
            'firstname',
            [
                'header' => __('First Name'),
                'index' => 'firstname',
                'type' => 'text',
            ]
        );
        $this->addColumn(
            'lastname',
            [
                'header' => __('Last Name'),
                'index' => 'lastname',
                'type' => 'text',
            ]
        );
        $this->addColumn(
            'grand_total',
            [
                'header' => __('Grand Total'),
                'index' => 'grand_total',
                'type' => 'currency',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'text',
                'options' => $this->orderConfig->getStatuses(),
            ]
        );
        $this->addColumn(
            'view',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => [
                            'base' => 'sales/order/view',
                        ],
                        'field' => 'order_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($item) {
        return $this->getUrl('sales/order/view', ['order_id' => $item->getOrderId(), ]);
    }
    
    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/orderGrid', ['_current' => true]);
    }
    
}
