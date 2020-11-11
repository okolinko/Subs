<?php
namespace Toppik\Subscriptions\Model\ResourceModel\Profile\Grid\Suspended;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Event\ManagerInterface as EventManager;

class Collection extends SearchResult {
    
    protected $_fieldMap = [
        'store_id' => 'main_table.store_id',
        'status' => 'main_table.status',
        'created_at' => 'main_table.created_at',
        'updated_at' => 'main_table.updated_at',
        'grand_total' => 'main_table.grand_total',
        'order_grand_total' => 'sales_order.grand_total',
        'order_created_at' => 'sales_order.created_at',
        'order_increment_id' => 'sales_order.increment_id',
        'frequency_title' => 'main_table.frequency_length',
        'merchant_source' => 'main_table.merchant_source',
        'admin_id' => 'main_table.admin_id'
    ];
    
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'subscriptions_profiles',
        $resourceModel = 'Toppik\Subscriptions\Model\ResourceModel\Profile'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    
    public function addFieldToFilter($field, $condition = null) {
        if($field === 'customer_name') {
            parent::addFieldToFilter([
                'first_name' => 'customer_entity.firstname',
                'last_name' => 'customer_entity.lastname',
            ], [
                'first_name' => $condition,
                'last_name' => $condition,
            ]);
            return $this;
        }
        if(isset($this->_fieldMap[$field])) {
            $field = $this->_fieldMap[$field];
        }
        return parent::addFieldToFilter($field, $condition);
    }
    
    /**
     * @return $this
     */
    protected function _initSelect() {
        parent::_initSelect();
        /* Join Customer Name */
        $this->getSelect()
            ->where('main_table.status = ?', \Toppik\Subscriptions\Model\Profile::STATUS_SUSPENDED)
            ->joinLeft(
                ['customer_entity' => $this->getTable('customer_entity'), ],
                'main_table.customer_id = customer_entity.entity_id',
                [
                    'IF(customer_entity.entity_id, CONCAT_WS(" ", customer_entity.firstname, customer_entity.lastname), "Guest") as customer_name'
                ]
            );
        /* Join Order */
        $this->getSelect()
            ->joinLeft(
                ['sales_order' => $this->getTable('sales_order'), ],
                'main_table.last_order_id = sales_order.entity_id',
                ['sales_order.increment_id as order_increment_id', 'sales_order.grand_total as order_grand_total', 'sales_order.created_at as order_created_at']
            );
        return $this;
    }
    
}
